<?php namespace Waka\Cloudis\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;

/**
 * Montages Back-end Controller
 */
class Montages extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Utils.Behaviors.DuplicateModel',
        'Waka.Cloudis.Behaviors.PopupCloudis',
        'Waka.Utils.Behaviors.PopupActions',

    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $duplicateConfig = 'config_duplicate.yaml';

    public function __construct()
    {
        parent::__construct();

        //BackendMenu::setContext('Waka.Cloudis', 'cloudis', 'montages');
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Cloudis', 'montages');
    }
}
