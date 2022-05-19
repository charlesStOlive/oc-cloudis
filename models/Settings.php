<?php namespace Waka\Cloudis\Models;

use Model;

class Settings extends Model
{
    use \Waka\Cloudis\Classes\Traits\CloudiTrait;

    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'waka_cloudis_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public $attachOne = [
        'logo' => \Waka\Cloudis\Models\CloudiFile::class,
        'unknown' => \Waka\Cloudis\Models\CloudiFile::class,
        'unknown_user' => \Waka\Cloudis\Models\CloudiFile::class,
    ];

    public function beforeSave()
    {
        if ($this::get('police_1')) {
            if ($this::get('police_1') != $this::get('police_1_previous')) {
                $srcPath = $this::get('cloudinary_path') . '/police/police1';
                $options = [
                    "resource_type" => 'raw',
                    "type" => "authenticated",
                ];
                $cloudData = \Cloudder::upload(storage_path('app/media/' . $this::get('police_1')), $srcPath, $options);
                $result = $cloudData->getResult();
                $version = $result['version'];
                $this::set([
                    'police_1_version' => $version,
                    'police_1_previous' => $this::get('police_1'),
                    'police_1_src' => 'v' . $version . '/' . $srcPath,
                ]);
            }
        }
        if ($this::get('police_2')) {
            if ($this::get('police_2') != $this::get('police_2_previous')) {
                $srcPath = $this::get('cloudinary_path') . '/police/police2';
                $options = [
                    "resource_type" => 'raw',
                    "type" => "authenticated",
                ];
                $cloudData = \Cloudder::upload(storage_path('app/media/' . $this::get('police_2')), $srcPath, $options);
                $result = $cloudData->getResult();
                $version = $result['version'];
                $this::set([
                    'police_2_version' => $version,
                    'police_2_previous' => $this::get('police_2'),
                    'police_2_src' => 'v' . $version . '/' . $srcPath,
                ]);
            }
        }
        if ($this::get('police_3')) {
            if ($this::get('police_3') != $this::get('police_3_previous')) {
                $srcPath = $this::get('cloudinary_path') . '/police/police3';
                $options = [
                    "resource_type" => 'raw',
                    "type" => "authenticated",
                ];
                $cloudData = \Cloudder::upload(storage_path('app/media/' . $this::get('police_3')), $srcPath, $options);
                $result = $cloudData->getResult();
                $version = $result['version'];
                $this::set([
                    'police_3_version' => $version,
                    'police_3_previous' => $this::get('police_3'),
                    'police_3_src' => 'v' . $version . '/' . $srcPath,
                ]);
            }
        }
    }

    public function afterSave()
    {
        //$this->updateCloudiRelations('attach');
    }
}
