<?php namespace Waka\Cloudis\Classes\Traits;

use Waka\Cloudis\Classes\YamlParserRelation;
use \Waka\Cloudis\Models\Settings as CloudisSettings;
use \Waka\Informer\Models\Inform;

trait CloudiTrait
{
    /**
     *
     */
    public function testCloudis()
    {
        trace_log($this->cloudiStringKeyExist('logo'));
    }

    public function cloudiKeyExist($cloudiModel)
    {
        return in_array($cloudiModel, $this->getCloudiKeys());
    }

    public function cloudiStringKeyExist($string)
    {
        return in_array($string, $this->getCloudiStringKeys());
    }

    /**
     * Retourne les imageCloudi de ce modèle.
     */
    public function getCloudiKeys()
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile' && $this->{$key}) {
                array_push($cloudiKeys, $this->{$key});
            }
        }
        return $cloudiKeys;
    }

    public function getCloudiStringKeys()
    {
        $cloudiKeys = [];
        $cloudiImgs = $this->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile' && $this->{$key}) {
                array_push($cloudiKeys, $key);
            }
        }
        return $cloudiKeys;
    }

    public function getErrorImage()
    {
        return CloudisSettings::get('srcPath');
    }

    public function getCloudiUrl($src, $id = null, $version = null)
    {
        $modelMontage = $this;
        $model = new $this->data_source->modelClass;
        $modelId;
        if ($id) {
            $modelId = $id;
        } else {
            $modelId = $this->data_source->test_id;
        }
        $model = $model::find($modelId);
        $parser = new YamlParserRelation($modelMontage, $model);
        trace_log($parser);
        $options = $parser->options;
        $formatOption = $version ? $this->setFormat($version) : null;
        // si il y a un format particulier on le merge avec
        if ($formatOption) {
            array_push($options['transformation'], $formatOption);
        }

        return \Cloudder::secureShow($parser->src, $options);

    }
    public function getCloudiModelUrl($modelMontage, $version = null)
    {
        $model = $this;
        $parser = new YamlParserRelation($modelMontage, $model);
        $options = $parser->options;
        $formatOption = $version ? $this->setFormat($version) : null;
        // si il y a un format particulier on le merge avec
        if ($formatOption) {
            array_push($options['transformation'], $formatOption);
        }

        return \Cloudder::secureShow($parser->src, $options);
    }

    /**
     *
     */
    public function clouderDeleteAll()
    {
        // foreach ($this->cloudiImages as $cloudiImage) {
        //     if ($this->{$cloudiImage}) {
        //         $this->clouderDelete($cloudiImage);
        //     }

        // }
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
            $models = $this->data_source->modelClass::get();
            foreach ($models as $model) {
                $parser = new YamlParserRelation($this, $model);
                trace_log($model->name . " : " . $parser->errors . " , " . $attachOrDetach);
                $errors = $parser->errors ? true : false;
                trace_log($errors);
                $this->attachOrDetach($model, $this->id, $attachOrDetach, $errors);
            }
        } else {
            $shortName = (new \ReflectionClass($this))->getShortName();
            //trace_log($shortName);
            $montages = \Waka\Cloudis\Models\Montage::where('active', '=', true)
                ->whereHas('data_source', function ($query) use ($shortName) {
                    $query->where('model', '=', $shortName);
                })->get();
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

}
