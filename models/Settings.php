<?php namespace Waka\Crsm\Models;

use Model;


class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'waka_cloudis_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    
}