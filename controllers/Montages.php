<?php namespace Waka\Cloudis\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;

/**
 * Montage Back-end Controller
 */
class Montages extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Wutils.Behaviors.BtnsBehavior',
        'Backend.Behaviors.RelationController',
        //'Waka.Cloudis.Behaviors.MontageBehavior',
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $btnsConfig = 'config_btns.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['waka.cloudis.*'];
    //FIN DE LA CONFIG AUTO
    //startKeep/

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Cloudis', 'Montages');
    }

    //endKeep/
}

