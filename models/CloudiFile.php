<?php namespace Waka\Cloudis\Models;

use Backend\Controllers\Files;
use Config;
use October\Rain\Database\Attach\File as FileBase;
use Storage;
use Url;

/**
 * File attachment model
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class CloudiFile extends FileBase
{
    /**
     * @var string The database table used by the model.
     */
    protected $table = 'waka_cloudis_system_files';

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

        \Cloudder::upload($realPath, 'testtemp/' . $this->disk_name);

        $this->putFile($realPath, $this->disk_name);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumb($width, $height, $options = [])
    {
        $url = '';
        // if (!$this->isPublic() && class_exists(Files::class)) {
        //     $options = $this->getDefaultThumbOptions($options);
        //     // Ensure that the thumb exists first
        //     parent::getThumb($width, $height, $options);

        //     // Return the Files controller handler for the URL
        //     $url = Files::getThumbUrl($this, $width, $height, $options);
        // } else {
        //     $url = parent::getThumb($width, $height, $options);
        // }
        trace_log('testtemp/' . $this->disk_name);
        //$version = 'thumb-' . $width . '-' . $height;
        $version = 'thumb-100-35';
        $formatOption = $version ? $this->setFormat($version) : null;
        trace_log($formatOption);
        return \Cloudder::secureShow('testtemp/' . $this->disk_name, $formatOption);
    }

    public function getUrl($version)
    {
        return \Cloudder::secureShow('testtemp/' . $this->disk_name, $formatOption);
    }

    public function deleteCloudi()
    {

        \Cloudder::destroy('testtemp/' . $this->disk_name, ['invalidate' => true]);
        $this->delete();
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
            'thumb' => [
                "gravity" => "face",
                "crop" => "thumb",
                "format" => "png",
            ],
            'thumbPng' => [
                "gravity" => "face",
                "crop" => "thumb",
                "format" => "png",
            ],
            'jpg' => [
                "crop" => 'fill',
                "format" => "jpg",
            ],
            'png' => [
                "crop" => 'fill',
                "format" => 'png',
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
