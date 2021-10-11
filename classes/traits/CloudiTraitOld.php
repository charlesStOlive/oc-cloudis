<?php namespace Waka\Cloudis\Classes\Traits;

use Waka\Cloudis\Classes\YamlParserRelation;
use Waka\Utils\Classes\DataSource;
use \Waka\Cloudis\Models\Settings as CloudisSettings;
use \Waka\Informer\Models\Inform;

trait CloudiTraitSTOP
{


    /**
     *
     */

    public function cloudiKeyExist($cloudiModel, $strict = true)
    {
        return in_array($cloudiModel, $this->getCloudiKeys($strict));
    }

    public function cloudiStringKeyExist($string, $strict = true)
    {
        return in_array($string, $this->getCloudiStringKeys($strict));
    }

    /**
     * Retourne les imageCloudi de ce modèle.
     */
    public function getCloudiKeys($strict = true)
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;

        foreach ($cloudiImgs as $key => $value) {
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
     * Retourne uniquement le nom de l'image
     */
    public function getCloudiStringKeys($strict = true)
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($strict) {
                if ($value == 'Waka\Cloudis\Models\CloudiFile' && $strict ? $this->{$key} : true) {
                    $cloudiKeys[$key] = $key;
                }
            } else {
                if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                    $cloudiKeys[$key] = $key;
                }
            }
        }
        $montages = $this->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $cloudiKeys['montages'][$montage->slug] = $montage->id;
            }
        }
        return $cloudiKeys;
    }

    public function getCloudiStringId($strict = true)
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($strict) {
                if ($value == 'Waka\Cloudis\Models\CloudiFile' && $strict ? $this->{$key} : true) {
                    $cloudiKeys[$key] = 'cloudi-' . $key;
                }
            } else {
                if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                    $cloudiKeys[$key] = 'cloudi-' . $key;
                }
            }
        }
        $montages = $this->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $cloudiKeys['montages'][$montage->slug] = 'montage-' . $montage->id;
            }
        }
        return $cloudiKeys;
    }

    /**
     * Retourne Key nom de l'image et value Model Image
     */
    public function getCloudiKeysObjects()
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                if ($this->{$key}) {
                    $cloudiKeys[$key] = $this->{$key}->cloudiId;
                } else {
                    $cloudiKeys[$key] = $this->getErrorImage();
                }
            }
        }
        $montages = $this->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $cloudiKeys['montages'][$montage->slug] = $montage->id;
            }
        }

        return $cloudiKeys;
    }

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

    public function getCloudiUrl($id = null, $version = null)
    {
        $modelMontage = $this;
        $ds = \DataSources::find($this->data_source);
        $model = $ds->getModel($id);
        $parser = new YamlParserRelation($modelMontage, $model);

        //trace_log($parser->options);

        if (!$parser->options) {
            return $this->getUrlErrorImage();
        }

        $options = $parser->options;
        $formatOption = $version ? $this->setFormat($version) : null;

        //trace_log($formatOption);
        // si il y a un format particulier on le merge avec
        if ($formatOption) {
            array_push($options['transformation'], $formatOption);
        }

        //trace_log($options);

        return \Cloudder::secureShow($parser->src, $options);
    }
    public function getCloudiModelUrl($modelMontage, $opt = null)
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
        // trace_log($options);

        return \Cloudder::secureShow($parser->src, $options);
    }

    /**
     *
     */
    public function clouderDeleteAll()
    {
        $imgs = $this->getCloudiKeys();

        foreach ($imgs as $img) {
            $img->deleteCloudi();
        }
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
    public function updateCLoudiRelationsFromMontage()
    {
        //trace_log("updateCLoudiRelationsFromMontage : " . $this->active);
        if ($this->active) {
            $this->updateCloudiRelations('attach');
        } else {
            $this->updateCloudiRelations('detach');
        }
    }
    public function updateCloudiRelations($attachOrDetach = 'attach')
    {
        //trace_log('updateCloudiRelations : ');
        $mainClass = get_class($this);
        if ($mainClass == 'Waka\Cloudis\Models\Montage') {
            $ds = \DataSources::find($this->data_source);
            $models = $ds->class::get();
            foreach ($models as $model) {
                $parser = new YamlParserRelation($this, $model);
                // trace_log($model->name . " : " . $parser->errors . " , " . $attachOrDetach);
                $errors = $parser->errors ? true : false;
                // trace_log($errors);
                $this->attachOrDetach($model, $this->id, $attachOrDetach, $errors);
            }
        } else {
            $shortName = (new \ReflectionClass($this))->getShortName();
            $ds =  \DataSources::findByClass(get_class($this));
            $montages = \Waka\Cloudis\Models\Montage::where('active', '=', true)
                ->where('data_source', $ds->code)->get();
            //trace_log($montages->toArray());
            foreach ($montages as $montage) {
                //trace_log($montage->slug);
                $parser = new YamlParserRelation($montage, $this);
                //trace_log($parser->errors);
                $errors = $parser->errors ? true : false;
                $this->attachOrDetach($this, $montage->id, $attachOrDetach, $errors);
            }
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

    public function getCloudisList($relation = null)
    {
        $modelClassName = get_class($this);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];
        if (!$relation) {
            $relation = 'self';
        }

        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                $img = [
                    'field' => $key,
                    'type' => 'cloudi',
                    'relation' => $relation,
                    'key' => $shortName . $key,
                    'name' => $shortName . ' : ' . $key,
                ];
                array_push($cloudiKeys, $img);
            }
        }
        return $cloudiKeys;
    }
    public function getCloudiMontagesList($relation = null)
    {
        $modelClassName = get_class($this);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];
        if (!$relation) {
            $relation = 'self';
        }

        $montages = $this->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $img = [
                    'id' => $montage->id,
                    'type' => 'montage',
                    'relation' => $relation,
                    'key' => $shortName . $montage->id,
                    'name' => 'Montage : ' . $montage->name,
                ];
                array_push($cloudiKeys, $img);
            }
        }
        return $cloudiKeys;
    }
}
