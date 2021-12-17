<?php namespace Waka\Cloudis\Classes\Traits;

use Waka\Cloudis\Classes\YamlParserRelation;
use Waka\Utils\Classes\DataSource;
use \Waka\Cloudis\Models\Settings as CloudisSettings;
use \Waka\Informer\Models\Inform;

trait CloudiTrait
{
    /*
     * Constructor
     */
    public static function bootCloudiTrait()
    {
        static::extend(function ($model) {
            /*
             * Define relationships
             */
            $model->morphToMany['montages'] = [
                'Waka\Cloudis\Models\Montage',
                'name' => 'montageable',
                'table' => 'waka_cloudis_montageables',
                'pivot' => ['errors'],
                'delete' => true,
            ];

            // $model->bindEvent('model.afterSave', function () use ($model) {
            //     $model->updateCloudiRelations('attach');
            // });

            // $model->bindEvent('model.beforeDelete', function () use ($model) {
            //     $model->clouderDeleteAll();
            // });
        });
    }

    /**
     *
     */
    public function getMontage($modelMontage, $opt = null)
    {
        $model = $this;
        $parser = new YamlParserRelation($modelMontage, $model);
        $options = $parser->options;
        //$formatOption = $version ? $this->setFormat($version) : null;
        // si il y a un format particulier on le merge avec
        if (!$parser->options) {
            return $this->getUrlErrorImage();
        }
        if ($opt) {
            array_push($options['transformation'], $opt);
        }
        if(!$parser->src) {
            //Si la source n'est pas trouvé
           $parser->src = $this->getErrorImage();
        }
        $url = \Cloudder::secureShow($parser->src, $options);

        return $url;
    }

    /**
     *
     */
    public function getErrorImage()
    {
        $cloudiSettings = CloudisSettings::instance();
        return $cloudiSettings->unknown->cloudiId;
    }

    public function getUrlErrorImage()
    {
        $cloudiSettings = CloudisSettings::instance();
        return $cloudiSettings->unknown->getUrl();
    }

    /**
     * Supprime tout les cloudis
     */
    public function clouderDeleteAll()
    {
        $imgs = $this->getCloudiKeys();
        //trace_log($imgs);

        foreach ($imgs as $img) {
            $img->deleteCloudi();
        }
    }

    /**
     * Retourne les imageCloudi de ce modèle.
     */
    public function getCloudiKeys($strict = true)
    {
        $cloudiKeys = [];
        $attachOnes = $this->attachOne;

        foreach ($attachOnes as $key => $value) {
            if ($strict) {
                if ($value == 'Waka\Cloudis\Models\CloudiFile' && $this->{$key}) {
                    array_push($cloudiKeys, $this->{$key});
                }
            } else {
                if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                    array_push($cloudiKeys, $this->{$key});
                }
            }
        }
        return $cloudiKeys;
    }

    /**
     * Traduction des couleurs pour cloudinary
     */
    public function getCloudiPrimaryColorAttribute()
    {
        if ($this->primary_color) {
            return substr($this->primary_color, 1);
        } else {
            return substr(CloudisSettings::get('primary_color'), 1);
        }
    }
    public function getCloudiSecondaryColorAttribute()
    {
        if ($this->secondary_color) {
            return substr($this->secondary_color, 1);
        } else {
            return substr(CloudisSettings::get('secondary_color'), 1);
        }
    }

    // public function updateCloudiRelations($attachOrDetach = 'attach')
    // {
    //     //trace_log('updateCloudiRelations : ');
    //     $mainClass = get_class($this);

    //     $shortName = (new \ReflectionClass($this))->getShortName();
    //     $ds = \DataSources::findByClass(get_class($this));
    //     $montages = \Waka\Cloudis\Models\Montage::where('active', '=', true)
    //         ->where('data_source', $ds->code)->get();
    //     //trace_log($montages->toArray());
    //     foreach ($montages as $montage) {
    //         //trace_log($montage->slug);
    //         $parser = new YamlParserRelation($montage, $this);
    //         //trace_log($parser->errors);
    //         $errors = $parser->errors ? true : false;
    //         $this->attachOrDetach($this, $montage->id, $attachOrDetach, $errors);
    //     }
    // }
    // public function attachOrDetach($model, $montageId, $attachOrDetach, $errors)
    // {
    //     //trace_log('attachOrDetach : '.$attachOrDetach);
    //     if ($attachOrDetach == 'attach') {
    //         if (!$model->montages()->find($montageId)) {
    //             $model->montages()->attach($montageId, ['errors' => $errors]);
    //         } else {
    //             $model->montages()->updateExistingPivot($montageId, ['errors' => $errors]);
    //         }
    //     }
    //     if ($attachOrDetach == 'detach') {
    //         if ($model->montages()->find($montageId)) {
    //             $model->montages()->detach($montageId);
    //         }
    //     }
    // }
}
