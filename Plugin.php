<?php namespace Waka\Cloudis;

use Backend;
use Event;
use Lang;
use System\Classes\PluginBase;
use View;

/**
 * Cloudis Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Waka.Utils',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Cloudis',
            'description' => 'No description provided yet...',
            'author' => 'Waka',
            'icon' => 'icon-leaf',
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

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'getCloudiBaseUrl' => function ($twig, $key = false, $size = false) {
                    trace_log("twig");
                    return $twig->getCloudiBaseUrl('main_image', 'jpg-400-350');
                    //
                },
                'getCloudiMontageUrl' => function ($twig, $key = false, $size = false) {
                    trace_log("twig");
                    return $twig->getCloudiBaseUrl('main_image', 'jpg-400-350');
                    //
                },
                'imageHeight' => function ($image) {
                    if (!$image instanceof Image) {
                        $image = new Image($image);
                    }
                    return getimagesize($image->getCachedImagePath())[1];
                },
            ],
        ];
    }

    public function getCloudiUrl($cloudi)
    {
        return get_class($cloudi);
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('backend.down.update', function ($controller) {
            if (in_array('Waka.Cloudis.Behaviors.PopupCloudis', $controller->implement)) {
                $data = [
                    'model' => $modelClass = str_replace('\\', '\\\\', get_class($controller->formGetModel())),
                    'modelId' => $controller->formGetModel()->id,
                ];
                return View::make('waka.cloudis::cloudisbutton')->withData($data);;
            }
        });
        Event::listen('popup.actions.line1', function ($controller, $model, $id) {
            if (in_array('Waka.Cloudis.Behaviors.PopupCloudis', $controller->implement)) {
                $data = [
                    'model' => str_replace('\\', '\\\\', $model),
                    'modelId' => $id,
                ];
                return View::make('waka.cloudis::cloudisbutton')->withData($data);;
            }
        });

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

    public function registerFormWidgets(): array
    {
        return [
            'Waka\Cloudis\FormWidgets\MontagesList' => 'montagelist',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'waka.cloudis.admin.super' => [
                'tab' => 'Waka',
                'label' => 'Super Administrateur de Cloudi',
            ],
            'waka.cloudis.admin' => [
                'tab' => 'Waka',
                'label' => 'Administrateur de Cloudi',
            ],
            'waka.cloudis.user' => [
                'tab' => 'Waka',
                'label' => 'Utilisateur de cloudi',
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
                'label' => 'Cloudis',
                'url' => Backend::url('waka/cloudis/mycontroller'),
                'icon' => 'icon-leaf',
                'permissions' => ['waka.cloudis.*'],
                'order' => 500,
            ],
        ];
    }
    public function registerSettings()
    {
        return [
            'montage' => [
                'label' => Lang::get('waka.cloudis::lang.menu.label'),
                'description' => Lang::get('waka.cloudis::lang.menu.description'),
                'category' => Lang::get('waka.cloudis::lang.menu.category'),
                'icon' => 'icon-object-group',
                'permissions' => ['waka.cloudis.admin'],
                'url' => Backend::url('waka/cloudis/montages'),
                'order' => 1,
            ],
            'cloudis_settings' => [
                'label' => Lang::get('waka.cloudis::lang.settings.label'),
                'description' => Lang::get('waka.cloudis::lang.settings.description'),
                'category' => Lang::get('waka.crsm::cloudis.settings.category'),
                'icon' => 'icon-cog',
                'class' => 'Waka\Cloudis\Models\Settings',
                'order' => 1,
                'permissions' => ['waka.cloudis.admin'],
            ],
        ];
    }
}
