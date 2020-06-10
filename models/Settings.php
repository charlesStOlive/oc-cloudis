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

    public function afterSave()
    {
        $this->updateCloudiRelations('attach');
    }

    public function beforeSave()
    {

    }

}
