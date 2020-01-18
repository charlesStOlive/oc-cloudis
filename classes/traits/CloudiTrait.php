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
    public function checkCloudisFilesChanges() {
        if(!count($this->cloudiImages)>0) {
            return;
        } 
        else {
            foreach($this->cloudiImages as $cloudiImage) {
                //trace_log($cloudiImage);
                $this->checkCloudisFileChange($cloudiImage);
            }
        }
    }
    
    public function checkCloudisFileChange($src) {
        trace_log('checkCloudisFileChange');
        //trace_log("analyse de l'image ".$src);
        $newVersion = $this->getDeferredCloudiImage($src);

        if(!$newVersion && !$this->{$src}) {
            trace_log('il n y as pas de fichier on ferme');
            //il n' y a pas de fichier on ferme
            return false;
        }
        // si la nouvelle version n'existe plus et il existe une ancienne version on detruit l'image sur cloudi
        if(!$newVersion && $this->{$src}) {
            trace_log('il n y as plus de fichier on efface');
            $this->updateCloudiRelations('detach');
            $this->clouderDelete($src);
            return false;
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
                $this->updateCloudiRelations('attach');
                return true;
            } else {
                trace_log('l ancienne version et la nouvelle sont les memes');
                return false;
            }
        } else {
            trace_log('c est une nouvelle');
            //Nouvelle image
            $this->clouderUpload($newVersion, $src);
            $this->updateCloudiRelations();
            return true;
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
    
    /**
     * $delete sur cloudder
     */
    public function clouderDelete($src) 
    {
        $options = [
            "invalidate" => true
        ];
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
        $targetModel = new $this->data_source->modelClass;
        $targetModelId;
        if($id) {
            $targetModelId = $id;
        } else {
            $targetModelId = $this->data_source->test_id;
        }
        $parser = new YamlParserRelation();
        //
        $options = $parser->parse($this, $targetModelId, $targetModel);
        //
        return Cloudder::secureShow($this->getCloudiId($src), $options);
    }
    public function getCloudiModelUrl($src, $montages, $id)
    {
        $targetModelId = $id;
        $parser = new YamlParserRelation();
        $options = $parser->parse($montages, $targetModelId, $this);
        return Cloudder::secureShow($this->getCloudiId($src), $options);
    }
    
    public function getCloudiRowUrl($src, $height=30) 
    {
        // if(!in_array($src, $this->cloudiImages)) {
        //     throw new ApplicationException('The source doesnt exist CloudiTrait');
        // } 
        $url = $this->getCloudiId($src, false);
        trace_log('getCloudiRowUrl');
        trace_log($url);
        $options =  [
            "gravity"=>"face",
            "height"=>$height,
            "crop"=>"thumb",
            "format"=>"jpg"
        ];
        trace_log(Cloudder::secureShow($url, $options));
        return Cloudder::secureShow($url, $options);
    }
    public function getCloudiBase($src) 
    {
        return Cloudder::secureShow($src);
    }

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
            $timeStamp = str_slug($this->getDeferredCloudiImage($src)->created_at);
        } else {
            $timeStamp = str_slug($this->{$src}->created_at);
        }
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
        $url = $this->getCloudiRowUrl($src);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        //  Get the HTML or whatever is linked in $url. 
        $response = curl_exec($handle);
        if(curl_getinfo($handle, CURLINFO_HTTP_CODE) == "200") {
            curl_close($handle);
            return true;
        } else {
            curl_close($handle);
            return false;
        }
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
    private function updateCloudiRelations($attachOrDetach='attach') {
        //trace_log('updateCloudiRelations');
        $mainClass = get_class($this);
        if($mainClass == 'Waka\Cloudis\Models\Montage') {
            $models = $this->data_source->modelClass::get();
            foreach($models as $model) {
                $this->attachOrDetach($model, $this->id, $attachOrDetach);
                // if(!$model->montages()->find($this->id)) {
                // $model->montages()->attach($this->id);
                // }
            }
        } 
        else {
            $shortName = (new \ReflectionClass($this))->getShortName();
            //trace_log($shortName);
            $montages = \Waka\Cloudis\Models\Montage::whereHas('data_source', function ($query) use($shortName) {
                $query->where('model', '=', $shortName);
            })->get(['id']);
            foreach($montages as $montage) {
                $this->attachOrDetach($this, $montage->id, $attachOrDetach);
                // if(!$this->montages()->find($montage->id)) {
                //     $this->montages()->attach($montage->id);
                // }
                
            }
        } 
    }
    public function attachOrDetach($model, $montageId, $attachOrDetach) {
        //trace_log('attachOrDetach : '.$attachOrDetach);
        if($attachOrDetach = 'attach') {
            if(!$model->montages()->find($montageId)) {
                $model->montages()->attach($montageId);
            }
        }
        if($attachOrDetach = 'detach') {
            if($model->montages()->find($montageId)) {
                $model->montages()->detach($montageId);
            }
        }
        

    }

}