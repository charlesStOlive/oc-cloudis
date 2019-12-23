<?php namespace Waka\Cloudis;

use Backend;
use System\Classes\PluginBase;

/**
 * Cloudis Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Cloudis',
            'description' => 'No description provided yet...',
            'author'      => 'Waka',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {

    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Waka\Cloudis\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'waka.cloudis.some_permission' => [
                'tab' => 'Cloudis',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return []; // Remove this line to activate

        return [
            'cloudis' => [
                'label'       => 'Cloudis',
                'url'         => Backend::url('waka/cloudis/mycontroller'),
                'icon'        => 'icon-leaf',
                'permissions' => ['waka.cloudis.*'],
                'order'       => 500,
            ],
        ];
    }
    public function registerSettings()
    {
        return [
            'montage' => [
                'label'       => Lang::get('waka.cloudis::lang.menu.label'),
                'description' => Lang::get('waka.cloudis::lang.menu.description'),
                'category'    => Lang::get('waka.cloudis::lang.menu.category'),
                'icon'        => 'icon-paper-plane',
                'url'         => Backend::url('waka/cloudis/montages'),
                'order'       => 1,
            ]
        ];
    }
}
