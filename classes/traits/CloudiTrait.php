<?php namespace Waka\Cloudis\Classes\Traits;

use \Waka\Informer\Models\Inform;
use Cloudder;
use \Mexitek\PHPColors\Color as ColorManipulationClass;
use Waka\Cloudis\Classes\YamlParserRelation;
use ApplicationException;

trait CloudiTrait
{
    /**
     * return system file if new 
     */
    public function checkModelCloudisFilesChanges() {
        $filesUpdate = [];
        if(!count($this->cloudiImages)>0) {
            return $filesUpdate;
        } 
        else {
            foreach($this->cloudiImages as $cloudiImage) {
                $filesUpdate[$cloudiImage] = $this->checkCloudisFileChange($cloudiImage);
            }
            return $filesUpdate;
        }
    }
    public function checkMontageChanges() {
        $filesUpdate = [];
        if(!$this->use_files) {
            return $filesUpdate;
        } 
        else {
            foreach($this->cloudiImages as $cloudiImage) {
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
    
    public function checkCloudisFileChange($src) {
        trace_log('checkCloudisFileChange');
        //trace_log("analyse de l'image ".$src);
        $newVersion = $this->getDeferredCloudiImage($src);

        if(!$newVersion && !$this->{$src}) {
            trace_log('il n y as pas de fichier on ferme');
            //il n' y a pas de fichier on ferme
            return null;
        }
        // si la nouvelle version n'existe plus et il existe une ancienne version on detruit l'image sur cloudi
        if(!$newVersion && $this->{$src}) {
            trace_log('il n y as plus de fichier on efface');
            //$this->updateCloudiRelations('detach');
            $this->clouderDelete($src);
            return 'delete';
        }
        //si il y a une ancienne version
        if($this->{$src}) {
            trace_log('il y a une ancienne version');
            $oldVersion = $this->{$src};
            if($oldVersion->created_at != $newVersion->created_at ) {
                trace_log('l ancienne version et la nouvelle sont differentes');
                //remplacement d'image
                $this->clouderDelete($src);
                $this->clouderUpload($newVersion, $src);
                //$this->updateCloudiRelations('attach');
                return 'update';
            } else {
                trace_log('l ancienne version et la nouvelle sont les memes');
                return 'update';
            }
        } else {
            trace_log('c est une nouvelle');
            //Nouvelle image
            $this->clouderUpload($newVersion, $src);
            //$this->updateCloudiRelations('attach');
            return 'new';
        }
    }
   
    /**
     * 
     */
    public function uploadToCloudinary($srcs) {
        trace_log("'UploadToCloudinary");
        foreach($srcs as $src) {
            trace_log($src);
            $this->clouderUpload($this->{$src}, $src);
            $this->updateCloudiRelations('attach');
        } 
    }
    public function clouderDeleteAll() {
        foreach($this->cloudiImages as $cloudiImage) {
            $this->clouderDelete($cloudiImage);
        }
    }
    
    /**
     * $delete sur cloudder
     */
    public function clouderDelete($src) 
    {
        trace_log("delete ".$src );
        $options = [
            "invalidate" => true
        ];
        $this->{$src}->delete();
        trace_log($this->getCloudiId($src, false));
        Cloudder::destroy($this->getCloudiId($src, false), $options);
    }
    /**
     * $charge sur cloudder
     */
    public function clouderUpload($file, $src) 
    {
        Cloudder::upload($file->getLocalPath(), $this->getCloudiId($src));
    }
    public function getCloudiUrl($src, $id=null)
    {
        $modelMontage = $this;
        $model = new $this->data_source->modelClass;
        $modelId;
        if($id) {
            $modelId = $id;
        } else {
            $modelId = $this->data_source->test_id;
        }
        $model = $model::find($modelId);
        $parser = new YamlParserRelation($modelMontage, $model);
        return Cloudder::secureShow($parser->src, $parser->options);
        
    }
    public function getCloudiModelUrl($modelMontage)
    {
        $model = $this;
        $parser = new YamlParserRelation($modelMontage, $model);
        return Cloudder::secureShow($parser->src, $parser->options);
    }
    
    public function getCloudiRowUrl($src, $height=30) 
    {
        // if(!in_array($src, $this->cloudiImages)) {
        //     throw new ApplicationException('The source doesnt exist CloudiTrait');
        // } 
        $url = $this->getCloudiId($src, false);
        $options =  [
            "gravity"=>"face",
            "height"=>$height,
            "crop"=>"thumb",
            "format"=>"jpg"
        ];
        trace_log(Cloudder::secureShow($url, $options));
        return Cloudder::secureShow($url, $options);
    }
    // public function getCloudiBase($src) 
    // {
    //     return Cloudder::secureShow($src);
    // }

    /**
     * $field nom du champs qui portera le nom
     */
    public function getCloudiId($src, $deffered=true) 
    {
        $srcPath = env('CLOUDINARY_PATH');
        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->cloudiSlug];
        $timeStamp = null;
        if($deffered) {
            $timeStamp = $this->getDeferredCloudiImage($src)->created_at ?? null;
            $timeStamp = str_slug($timeStamp);
        } else {
            $timeStamp = $this->{$src}->created_at ?? null;
            $timeStamp = str_slug($timeStamp);
        }
        if(!$timeStamp) return null;
        return $srcPath.'/'.$modelName.'/'.$modelSlug.'-'.$src.'-v'.$timeStamp;
    }

    /**
     *  Retourne l'attribut d'une imgCloudi en deffered
     */
    public function getDeferredCloudiImage($src) {
        return $this->{$src}()->withDeferred($this->sessionKey)->first();
    }

    /**
     * Image existe
     */
    public function getCloudiExiste($src) {
        //trace_log($src);
        if(!$this->{$src}) return false;
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
    public function getCloudiPrimaryColorAttribute() {
        if($this->primary_color) {
            return substr($this->primary_color, 1);
        } else {
            return null;
        }
        

    }
    public function getCloudiSecondaryColorAttribute() {
        if($this->secondary_color) {
            return substr($this->secondary_color, 1);
        } else {
            return null;
        }
    }
    public function updateCLoudiRelationsFromMontage() 
    {
        trace_log("update cloudi relation from montage");
        trace_log("active : ".$this->active);
        if($this->active) {
            $this->updateCloudiRelations('attach');
        } else {
            $this->updateCloudiRelations('detach');
        }

    }
    public function updateCloudiRelations($attachOrDetach='attach') {
        //trace_log('updateCloudiRelations');
        $mainClass = get_class($this);
        if($mainClass == 'Waka\Cloudis\Models\Montage') {
            $models = $this->data_source->modelClass::get();
            foreach($models as $model) {
                $parser = new YamlParserRelation($this, $model);
                trace_log($model->name." : ".$parser->errors." , ".$attachOrDetach);
                if(!$parser->errors) {
                    $this->attachOrDetach($model, $this->id, $attachOrDetach);
                } else {
                    $this->attachOrDetach($model, $this->id, 'detach');
                }
            }
        } 
        else {
            $shortName = (new \ReflectionClass($this))->getShortName();
            //trace_log($shortName);
            $montages = \Waka\Cloudis\Models\Montage::where('active', '=', true)
            ->whereHas('data_source', function ($query) use($shortName) {
                $query->where('model', '=', $shortName);
            })->get();
            foreach($montages as $montage) {
                $parser = new YamlParserRelation($montage, $this);
                trace_log($parser->errors);
                if(!$parser->errors) {
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
    public function attachOrDetach($model, $montageId, $attachOrDetach) {
        //trace_log('attachOrDetach : '.$attachOrDetach);
        if($attachOrDetach == 'attach') {
            if(!$model->montages()->find($montageId)) {
                trace_log($model->name." attach : ".$montageId);
                $model->montages()->attach($montageId);
            }
        }
        if($attachOrDetach == 'detach') {
            if($model->montages()->find($montageId)) {
                trace_log($model->name." detach : ".$montageId);
                $model->montages()->detach($montageId);
            }
        }
        

    }

}