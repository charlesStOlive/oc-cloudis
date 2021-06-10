<?php namespace Waka\Cloudis\Models;

use Backend\Controllers\Files;
use Config;
use File as FileHelper;
use Winter\Storm\Database\Attach\File as FileBase;
use Storage;
use Url;
use \Waka\Cloudis\Models\Settings as CloudisSettings;

/**
 * File attachment model
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class CloudiFile extends FileBase// copy de \Modules\System\Files et adaptation.
{
    /**
     * @var string The database table used by the model.
     */
    protected $table = 'waka_cloudis_system_files';

    public function afterDelete()
    {
        //trace_log("after delete in cloudi");
    }
    public function beforeDelete()
    {
        //trace_log("before delete in cloudi");
    }

    public function getCloudiPathAttribute()
    {
        return CloudisSettings::get('cloudinary_path');
    }

    public function fromPost($uploadedFile)
    {
        if ($uploadedFile === null) {
            return;
        }

        $this->file_name = $uploadedFile->getClientOriginalName();
        $this->file_size = $uploadedFile->getClientSize();
        $this->content_type = $uploadedFile->getMimeType();
        $this->disk_name = $this->getDiskName();

        /*
         * getRealPath() can be empty for some environments (IIS)
         */
        $realPath = empty(trim($uploadedFile->getRealPath()))
        ? $uploadedFile->getPath() . DIRECTORY_SEPARATOR . $uploadedFile->getFileName()
        : $uploadedFile->getRealPath();

        if (starts_with($this->content_type, 'video')) {
            \Cloudder::uploadVideo($realPath, $this->cloudiPath . '/' . $this->disk_name);
        } else {
            \Cloudder::upload($realPath, $this->cloudiPath . '/' . $this->disk_name);
        }

        //$this->putFile($realPath, $this->disk_name);

        return $this;
    }

    /**
     * Creates a file object from url
     * @param $url string URL
     * @param $filename string Filename
     * @return $this
     */
    public function fromUrl($url, $filename = null)
    {
        $this->disk_name = $this->getDiskName();

        if (empty($filename)) {
            $filename = FileHelper::basename($url);
        }

        $upload = \Cloudder::upload($url, $this->cloudiPath . '/' . $this->disk_name);
        $cloudiResult = $upload->getResult();

        if ($upload) {
            $this->file_name = $filename;
            $this->file_size = $cloudiResult['bytes'];
            $this->content_type = $cloudiResult['resource_type'] . '/' . $cloudiResult['format'];
            return $this;
        } else {
            return null;
        }
    }

    /**
     * Copy de la finction de FILEBASE pour enlever les extentions.
     */
    protected function getDiskName()
    {
        if ($this->disk_name !== null) {
            return $this->disk_name;
        }
        return $this->disk_name = str_replace('.', '', uniqid(null, true));
    }

    public function getCloudiUrl($width = 400, $height = 400, $format = "auto", $crop = "fill")
    {
        $formatOption = [];
        if($crop == "pad") {
            $formatOption = [
                "width" => $width,
                "height" => $height,
                "crop" => $crop,
                "background" => "auto",
                "quality" => "auto",
                "fetch_format" => $format,
            ];
        } else {
            $formatOption = [
                "width" => $width,
                "height" => $height,
                "crop" => $crop,
                "quality" => "auto",
                "fetch_format" => $format,
            ];
        }
       
        $formatOption['format'] = $format;
        return \Cloudder::secureShow($this->cloudiPath . '/' . $this->disk_name, $formatOption);
    }

    public function getVideoUrl($width = null, $height = null, $start_at = null, $crop = "fill")
    {
        $formatOption = [
            'resource_type' => 'video',
            "format" => "mp4",
        ];
        if ($width) {
            $formatOption['width'] = $width;
        }
        if ($height) {
            $formatOption['height'] = $height;
        }
        if ($start_at) {
            $formatOption['start_offset'] = $start_at;
        }
        //trace_log($this->disk_name);
        return \Cloudder::secureShow($this->cloudiPath . '/' . $this->disk_name, $formatOption);
    }

    /**
     * {@inheritDoc}
     */
    public function getThumb($width = 160, $height = 100, $options = [])
    {
        $version = 'png-' . $width . '-' . $height;
        $formatOption = $version ? $this->setFormat($version) : null;
        //trace_log($formatOption);
        return \Cloudder::secureShow($this->cloudiPath . '/' . $this->disk_name, $formatOption);
    }

    public function getColumnThumb($width = 75, $height = 30, $options = [])
    {
        $version = 'jpg-' . $width . '-' . $height;
        $formatOption = $version ? $this->setFormat($version) : null;
        //trace_log($formatOption);
        return \Cloudder::secureShow($this->cloudiPath . '/' . $this->disk_name, $formatOption);
    }

    public function getUrl($options = [])
    {
        return \Cloudder::secureShow($this->cloudiPath . '/' . $this->disk_name, $options);
    }

    public function deleteCloudi()
    {

        \Cloudder::destroy($this->cloudiPath . '/' . $this->disk_name, ['invalidate' => true]);
        $this->delete();
    }

    public function getCloudiIdAttribute()
    {
        return $this->cloudiPath . '/' . $this->disk_name;
    }

    public function getIdPathAttribute()
    {
        return $this->cloudiPath . ':' . $this->disk_name;
    }

    public function setFormat($vers = 'base')
    {
        if ($vers == 'base') {
            return null;
        }

        $options = explode('-', $vers);
        $height = null;
        $width = null;
        if (count($options) > 1) {
            $vers = $options[0];
            $width = $options[1] ?? null;
            $height = $options[2] ?? null;
        }
        $versions = [
            'png' => [
                "crop" => "pad",
                "format" => "png",
            ],
            'jpg' => [
                "crop" => 'pad',
                "format" => "jpg",
            ],
        ];
        $array = $versions[$vers];
        if (is_numeric($width)) {
            $array['width'] = $width;
        }

        if (is_numeric($height)) {
            $array['height'] = $height;
        }

        return $array;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($fileName = null)
    {
        $url = '';
        if (!$this->isPublic() && class_exists(Files::class)) {
            $url = Files::getDownloadUrl($this);
        } else {
            $url = parent::getPath($fileName);
        }

        return $url;
    }

    /**
     * If working with local storage, determine the absolute local path.
     */
    protected function getLocalRootPath()
    {
        return Config::get('filesystems.disks.local.root', storage_path('app'));
    }

    /**
     * Define the public address for the storage path.
     */
    public function getPublicPath()
    {
        $uploadsPath = Config::get('cms.storage.uploads.path', '/storage/app/uploads');

        if ($this->isPublic()) {
            $uploadsPath .= '/public';
        } else {
            $uploadsPath .= '/protected';
        }

        return Url::asset($uploadsPath) . '/';
    }

    /**
     * Define the internal storage path.
     */
    public function getStorageDirectory()
    {
        $uploadsFolder = Config::get('cms.storage.uploads.folder');

        if ($this->isPublic()) {
            return $uploadsFolder . '/public/';
        }

        return $uploadsFolder . '/protected/';
    }

    /**
     * Returns true if storage.uploads.disk in config/cms.php is "local".
     * @return bool
     */
    protected function isLocalStorage()
    {
        return Config::get('cms.storage.uploads.disk') == 'local';
    }

    /**
     * Returns the storage disk the file is stored on
     * @return FilesystemAdapter
     */
    public function getDisk()
    {
        return Storage::disk(Config::get('cms.storage.uploads.disk'));
    }
}
