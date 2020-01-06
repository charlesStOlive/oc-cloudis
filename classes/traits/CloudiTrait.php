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
                trace_log($cloudiImage);
                $this->checkCloudisFileChange($cloudiImage);
            }
        }
    }
    
    public function checkCloudisFileChange($src) {
        trace_log("analyse de l'image ".$src);
        $newVersion = $this->getDeferredCloudiImage($src);
        if(!$newVersion && !$this->{$src}) {
            //il n' y a pas de fichier on ferme
            return false;
        }
        // si la nouvelle version n'existe plus et il existe une ancienne version on detruit l'image sur cloudi
        if(!$newVersion && $this->{$src}) {
            $this->clouderDelete($src);
            return false;
        }
        //si il y a une ancienne version
        if($this->{$src}) {
            $oldVersion = $this->{$src};
            if($oldVersion->created_at != $newVersion->created_at ) {
                //remplacement d'image
                $this->clouderDelete($src);
                $this->clouderUpload($newVersion, $src);
                return true;
                //$this->updateCloudiRelations();
            } else {
                return false;
            }
        } else {
            //Nouvelle image
            $this->clouderUpload($newVersion, $src);
            return true;
            //$this->updateCloudiRelations();
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
        Cloudder::destroy($this->getCloudiIdOld($src), $options);
    }
    /**
     * $charge sur cloudder
     */
    public function clouderUpload($file, $src) 
    {
        Cloudder::upload($file->getLocalPath(), $this->getCloudiId($src));
    }
    public function getCloudiUrl($src) 
    {
        $targetModel = new $this->data_source->modelClass;
        $targetModelId = $targetModel->first()->id;
        $parser = new YamlParserRelation();
        //
        $options = $parser->parse($this, $targetModelId, $targetModel);
        //
        return Cloudder::secureShow($this->getCloudiId($src), $options);
    }
    public function getCloudiRowUrl($src, $width=30) 
    {
        // if(!in_array($src, $this->cloudiImages)) {
        //     throw new ApplicationException('The source doesnt exist CloudiTrait');
        // } 
        return 'fuck';
        $options =  [
            "gravity"=>"face",
            "width"=>$width,
            "crop"=>"thumb",
            "format"=>"jpg"
        ];
        return Cloudder::secureShow($src, $options);
    }
    public function getCloudiBase($src) 
    {
        return Cloudder::secureShow($src);
    }

    /**
     * $field nom du champs qui portera le nom
     */
    public function getCloudiId($src) 
    {
        $srcPath = env('CLOUDINARY_PATH');
        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->cloudiSlug];
        $timeStamp = str_slug($this->getDeferredCloudiImage($src)->created_at);
        return $srcPath.'/'.$modelName.'/'.$modelSlug.'-'.$src.'-v'.$timeStamp;
    }
    public function getCloudiIdOld($src) 
    {
        $srcPath = env('CLOUDINARY_PATH');
        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->cloudiSlug];
        $timeStamp = str_slug($this->{$src}->created_at);
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
        if(!$this->{$src}) return false;
        $url = $this->getCloudiBase($src);
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
        trace_log($attachOrDetach);
        $mainClass = get_class($this);
        if($mainClass == 'Waka\Cloudis\Models\Montages') {
            $targetModel = new $this->data_source->modelClass;
            $models = $targetModel->get();
            foreach($models as $model) {
                $this->attachOrDetach($model, $this->id, $attachOrDetach);
                // if(!$model->montages()->find($this->id)) {
                // $model->montages()->attach($this->id);
                // }
            }
        } 
        else {
            $shortName = (new \ReflectionClass($this))->getShortName();
            trace_log($shortName);
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