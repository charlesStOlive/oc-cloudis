<?php namespace Waka\Cloudis\Classes\Traits;

use ApplicationException;
use Cloudder;
use Waka\Cloudis\Classes\YamlParserRelation;
use \Waka\Informer\Models\Inform;

trait CloudiTrait
{
    /**
     * return system file if new
     */
    public function checkModelCloudisFilesChanges()
    {
        $filesUpdate = [];
        if (!count($this->cloudiImages) > 0) {
            return $filesUpdate;
        } else {
            foreach ($this->cloudiImages as $cloudiImage) {
                $filesUpdate[$cloudiImage] = $this->checkCloudisFileChange($cloudiImage);
            }
            return $filesUpdate;
        }
    }
    public function checkMontageChanges()
    {
        $filesUpdate = [];
        if (!$this->use_files) {
            return $filesUpdate;
        } else {
            foreach ($this->cloudiImages as $cloudiImage) {
                //trace_log($cloudiImage);
                $filesUpdate[$cloudiImage] = $this->checkCloudisFileChange($cloudiImage);
            }
            return $filesUpdate;
        }
    }
    /**
     * Goal : upload or not files on cloudinary
     * Return instruction for attaching or detaching relation
     */

    public function checkCloudisFileChange($src)
    {
        //trace_log("analyse de l'image ".$src);
        $newVersion = $this->getDeferredCloudiImage($src);

        //trace_log("cloudi existe : " . $this->getCloudiExiste($src));
        if (!$newVersion && !$this->getCloudiExiste($src)) {
            //trace_log('il n y as pas de fichier on ferme');
            //il n' y a pas de fichier on ferme
            return null;
        }
        // si la nouvelle version n'existe plus et il existe une ancienne version on detruit l'image sur cloudi
        if (!$newVersion && $this->getCloudiExiste($src)) {
            //trace_log('il n y as plus de fichier on efface');
            //$this->updateCloudiRelations('detach');
            $this->clouderDelete($src);
            return 'delete';
        }
        //si il y a une ancienne version
        if ($this->{$src}) {
            //trace_log('il y a une ancienne version');
            $oldVersion = $this->{$src};
            if ($oldVersion->created_at != $newVersion->created_at) {
                //trace_log('l ancienne version et la nouvelle sont differentes');
                //remplacement d'image
                //$this->clouderDelete($src); plus besoin du delete on remplace
                $this->clouderUpload($newVersion, $src);
                //$this->updateCloudiRelations('attach');
                return 'update';
            } else {
                //trace_log('l ancienne version et la nouvelle sont les memes, verificatiob');
                if (!$this->getCloudiExiste($src)) {
                    $this->clouderUpload($newVersion, $src);
                    return 'update';
                }
            }
        } else {
            //trace_log('c est une nouvelle');
            //Nouvelle image
            $this->clouderUpload($newVersion, $src);
            //$this->updateCloudiRelations('attach');
            return 'new';
        }
    }

    /**
     *
     */
    public function uploadToCloudinary($srcs)
    {
        foreach ($srcs as $src) {
            $this->clouderUpload($this->{$src}, $src);
            $this->updateCloudiRelations('attach');
        }
    }
    public function clouderDeleteAll()
    {
        foreach ($this->cloudiImages as $cloudiImage) {
            if ($this->{$cloudiImage}) {
                $this->clouderDelete($cloudiImage);
            }

        }
    }

    /**
     * $delete sur cloudder
     */
    public function clouderDelete($src)
    {
        $options = [
            "invalidate" => true,
        ];
        $this->{$src}->delete();
        $this->cloudis_files()->where('code', $src)->first()->delete();
        Cloudder::destroy($this->readCloudiId($src, false), $options);
    }
    /**
     * $charge sur cloudder
     */
    public function clouderUpload($file, $src)
    {
        //trace_log("Cloudder upload");
        $cloudData = Cloudder::upload($file->getLocalPath(), $this->readCloudiId($src));
        $result = $cloudData->getResult();
        $this->cloudis_files()->updateOrCreate(
            ['code' => $src],
            ['version' => $result['version'], 'code' => $src]
        );
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
        $options = $parser->options;
        $formatOption = $version ? $this->setFormat($version) : null;
        // si il y a un format particulier on le merge avec
        if ($formatOption) {
            array_push($options['transformation'], $formatOption);
        }

        return Cloudder::secureShow($parser->src, $options);

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

        return Cloudder::secureShow($parser->src, $options);
    }

    public function setFormat($vers = 'base')
    {
        if ($vers == 'base') {
            return null;
        }

        $options = explode('-', $vers);
        $height = null;
        $width = null;
        if (count($options) > 1) {
            $vers = $options[0];
            $width = $options[1] ?? null;
            $height = $options[2] ?? null;
        }
        $versions = [
            'thumb' => [
                "gravity" => "face",
                "crop" => "thumb",
                "format" => "jpg",
            ],
            'thumbPng' => [
                "gravity" => "face",
                "crop" => "thumb",
                "format" => "png",
            ],
            'jpg' => [
                "crop" => 'fill',
                "format" => "jpg",
            ],
            'png' => [
                "crop" => 'fill',
                "format" => 'png',
            ],
        ];
        $array = $versions[$vers];
        if (is_numeric($width)) {
            $array['width'] = $width;
        }

        if (is_numeric($height)) {
            $array['height'] = $height;
        }

        return $array;

    }

    public function getCloudiBaseUrl($src, $version = 'thumb--35')
    {
        $url = $this->getCloudiId($src, false);
        $formatOption = $version ? $this->setFormat($version) : null;
        return Cloudder::secureShow($url, $formatOption);
    }

    /**
     * $field nom du champs qui portera le nom
     */
    public function readCloudiId($src)
    {
        $srcPath = env('CLOUDINARY_PATH');
        if (!$srcPath) {
            throw new ApplicationException('CLOUDINARY_PATH problem, refresh your page');
        }

        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->cloudiSlug];
        return $srcPath . '/' . $modelName . '/' . $modelSlug . '-' . $src;

    }
    public function getCloudiId($src)
    {
        $srcPath = env('CLOUDINARY_PATH');
        if (!$this->getCloudiExiste($src)) {
            return null;
        }

        if (!$srcPath) {
            throw new ApplicationException('CLOUDINARY_PATH problem, refresh your page');
        }

        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->cloudiSlug];
        $version = null;
        $hasCoudiFiles = $this->cloudis_files ? $this->cloudis_files->count() : null;
        if ($hasCoudiFiles) {
            $version = $this->cloudis_files()->where('code', $src)->get()->first()->version ?? false;
        }

        if ($version) {
            return 'v' . $version . '/' . $srcPath . '/' . $modelName . '/' . $modelSlug . '-' . $src;
        } else {
            return $srcPath . '/' . $modelName . '/' . $modelSlug . '-' . $src;
        }
    }

    /**
     *  Retourne l'attribut d'une imgCloudi en deffered
     */
    public function getDeferredCloudiImage($src)
    {
        return $this->{$src}()->withDeferred($this->sessionKey)->first();
    }

    /**
     * Image existe
     */
    public function getCloudiExiste($src)
    {
        if (!$this->{$src}) {
            return false;
        }

        if (!$this->cloudis_files()->where('code', $src)->count()) {
            return false;
        }

        //trace_log("test de cloudiExiste");
        // $url = $this->getCloudiRowUrl($src);
        // $handle = curl_init($url);
        // curl_setopt($handle, CURLOPT_NOBODY, true);
        // //  Get the HTML or whatever is linked in $url.
        // $response = curl_exec($handle);
        // if(curl_getinfo($handle, CURLINFO_HTTP_CODE) == "200") {
        //     curl_close($handle);
        //     return true;
        // } else {
        //     curl_close($handle);
        //     return false;
        // }
        return true;
    }

    /**
     * Traduction des couleurs pour cloudinary
     */
    public function getCloudiPrimaryColorAttribute()
    {
        if ($this->primary_color) {
            return substr($this->primary_color, 1);
        } else {
            return null;
        }

    }
    public function getCloudiSecondaryColorAttribute()
    {
        if ($this->secondary_color) {
            return substr($this->secondary_color, 1);
        } else {
            return null;
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
                //trace_log($model->name . " : " . $parser->errors . " , " . $attachOrDetach);
                if (!$parser->errors) {
                    $this->attachOrDetach($model, $this->id, $attachOrDetach);
                } else {
                    $this->attachOrDetach($model, $this->id, 'detach');
                }
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
                if (!$parser->errors) {
                    $this->attachOrDetach($this, $montage->id, $attachOrDetach);
                } else {
                    $this->attachOrDetach($this, $montage->id, 'detach');
                }

                // if(!$this->montages()->find($montage->id)) {
                //     $this->montages()->attach($montage->id);
                // }

            }
        }
    }
    public function attachOrDetach($model, $montageId, $attachOrDetach)
    {
        //trace_log('attachOrDetach : '.$attachOrDetach);
        if ($attachOrDetach == 'attach') {
            if (!$model->montages()->find($montageId)) {
                //trace_log($model->name." attach : ".$montageId);
                $model->montages()->attach($montageId);
            }
        }
        if ($attachOrDetach == 'detach') {
            if ($model->montages()->find($montageId)) {
                //trace_log($model->name." detach : ".$montageId);
                $model->montages()->detach($montageId);
            }
        }

    }

}
