<?php namespace Waka\Cloudis;

use Backend;
use Event;
use Lang;
use System\Classes\PluginBase;
use View;
use Waka\Cloudis\Models\Biblio;
use Winter\Storm\Support\Collection;

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
                'getCloudiUrl' => function ($twig, $width = 400, $height = 400, $format = null, $crop = "fill") {
                    if(!$twig) {
                        \Log::warning('getCloudiUrl : twig null');
                        return null;
                    }
                    return $twig->getCloudiUrl($width, $height, $format, $crop);
                    //
                },
                'getCloudiMontageUrl' => function ($twig, $slug, $width = 400, $height = 200, $format = null, $crop = "fill", $gravity = "center") {
                    if(!$twig) {
                        \Log::warning('getCloudiMontageUrl : twig null');
                        return null;
                    }
                    $montage = \Waka\Cloudis\Models\Montage::where('slug', $slug)->first();
                    $opt = [
                        'width' => $width,
                        'height' => $height,
                        'format' => $format,
                        'crop' => $crop,
                        'gravity' => $gravity,
                        'quality' => 'auto',
                    ];
                    if ($montage && $twig) {
                        return $twig->getMontage($montage, $opt);
                    } else {
                        return 'error';
                    }

                    //
                },
                'imageHeight' => function ($image) {
                    if (!$image instanceof Image) {
                        $image = new Image($image);
                    }
                    return getimagesize($image->getCachedImagePath())[1];
                },
            ],
            'functions' => [
                // Using an inline closure
                'biblioVideo' => function ($code, $width = null, $height = null, $start_at = null) {
                    //trace_log($code);
                    $ressource = Biblio::where('slug', $code)->first();

                    //trace_log($ressource->srcv->getVideoUrl($width, $height, $start_at));
                    if ($ressource->srcv) {
                        return $ressource->srcv->getVideoUrl($width, $height, $start_at);
                    } else {
                        return null;
                    }
                },
                'biblioImage' => function ($code, $width = null, $height = null, $format = null, $crop = "fill") {
                    //trace_log($code);
                    //trace_log($crop);
                    $ressource = Biblio::where('slug', $code)->first();
                    if ($ressource) {
                        return $ressource->src->getCloudiUrl($width, $height, $format, $crop);
                    } else {
                        return null;
                    }
                    //trace_log($ressource->src->getCloudiUrl($width, $height, $format, $crop));
                },
            ],
        ];
    }

    // public function getCloudiUrl($cloudi)
    // {
    //     return get_class($cloudi);
    // }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('backend.update.prod', function ($controller) {
            if (in_array('Waka.Cloudis.Behaviors.PopupCloudis', $controller->implement)) {
                $data = [
                    'model' => $modelClass = str_replace('\\', '\\\\', get_class($controller->formGetModel())),
                    'modelId' => $controller->formGetModel()->id,
                ];
                return View::make('waka.cloudis::cloudisbutton')->withData($data);
                ;
            }
        });
        Event::listen('popup.actions.prod', function ($controller, $model, $id) {
            if (in_array('Waka.Cloudis.Behaviors.PopupCloudis', $controller->implement)) {
                $data = [
                    'model' => str_replace('\\', '\\\\', $model),
                    'modelId' => $id,
                ];
                return View::make('waka.cloudis::cloudisbutton')->withData($data);
                ;
            }
        });
        \Waka\Utils\Classes\Ds\DataSource::extend(function($ds) {
            $ds->addDynamicMethod('getImagesFilesFromMontage', function($code) use ($ds) {
                $code ? $code : $ds->code;
                return \Waka\Cloudis\Models\Montage::where('data_source', $code)->lists('name', 'id');
            });
                
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
            'Waka\Cloudis\FormWidgets\CloudiFileUpload' => 'cloudifileupload',
            'Waka\Cloudis\FormWidgets\BiblioList' => 'bibliolist',
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
                'tab' => 'Waka - Cloudi',
                'label' => 'Super Administrateur de Cloudi',
            ],
            'waka.cloudis.admin.base' => [
                'tab' => 'Waka - Cloudi',
                'label' => 'Administrateur de Cloudi',
            ],
            'waka.cloudis.user' => [
                'tab' => 'Waka - Cloudi',
                'label' => 'Utilisateur de cloudi',
            ],
        ];
    }

    public function registerWakaRules()
    {
        return [
            'asks' => [
                ['\Waka\Cloudis\WakaRules\Asks\FileCloudiLinked'],
                ['\Waka\Cloudis\WakaRules\Asks\MontageCloudi'],
            ],
            'fncs' => [
               
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
            'montages' => [
                'label' => Lang::get('waka.cloudis::lang.menu.label'),
                'description' => Lang::get('waka.cloudis::lang.menu.description'),
                'category' => Lang::get('waka.utils::lang.menu.settings_category_model'),
                'icon' => 'icon-object-group',
                'permissions' => ['waka.cloudis.admin.*'],
                'url' => Backend::url('waka/cloudis/montages'),
                'order' => 40,
            ],
            'cloudis_settings' => [
                'label' => Lang::get('waka.cloudis::lang.menu.settings'),
                'description' => Lang::get('waka.cloudis::lang.menu.settings_description'),
                'category' => Lang::get('waka.utils::lang.menu.settings_category'),
                'icon' => 'icon-file-image-o',
                'class' => 'Waka\Cloudis\Models\Settings',
                'order' => 115,
                'permissions' => ['waka.cloudis.admin.super'],
            ],
            'biblios' => [
                'label' => Lang::get('waka.cloudis::lang.menu.biblios'),
                'description' => Lang::get('waka.cloudis::lang.menu.biblios_desc'),
                'category' => Lang::get('waka.utils::lang.menu.settings_category_model'),
                'icon' => 'icon-picture-o',
                'permissions' => ['waka.cloudis.*'],
                'url' => Backend::url('waka/cloudis/biblios'),
                'order' => 40,
            ],
        ];
    }
}
