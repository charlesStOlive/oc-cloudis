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
            ];

            
            $model->bindEvent('model.afterSave', function () use ($model) {
                $model->updateCloudiRelations('attach');
            });

            $model->bindEvent('model.beforeDelete', function () use ($model) {
                $model->clouderDeleteAll();
            });
        });

        
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


    public function updateCloudiRelations($attachOrDetach = 'attach')
    {
        //trace_log('updateCloudiRelations : ');
        $mainClass = get_class($this);
        
        $shortName = (new \ReflectionClass($this))->getShortName();
        $ds = new DataSource(get_class($this), 'class');
        $montages = \Waka\Cloudis\Models\Montage::where('active', '=', true)
            ->where('data_source_id', $ds->id)->get();
        //trace_log($montages->toArray());
        foreach ($montages as $montage) {
            //trace_log($montage->slug);
            $parser = new YamlParserRelation($montage, $this);
            //trace_log($parser->errors);
            $errors = $parser->errors ? true : false;
            $this->attachOrDetach($this, $montage->id, $attachOrDetach, $errors);
        }
    }
    public function attachOrDetach($model, $montageId, $attachOrDetach, $errors)
    {
        //trace_log('attachOrDetach : '.$attachOrDetach);
        if ($attachOrDetach == 'attach') {
            if (!$model->montages()->find($montageId)) {
                $model->montages()->attach($montageId, ['errors' => $errors]);
            } else {
                $model->montages()->updateExistingPivot($montageId, ['errors' => $errors]);
            }
        }
        if ($attachOrDetach == 'detach') {
            if ($model->montages()->find($montageId)) {
                $model->montages()->detach($montageId);
            }
        }

    }



    
}
