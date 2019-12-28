<?php namespace Waka\Cloudis\Classes\Traits;

use \Waka\Informer\Models\Inform;
use Cloudder;
use \Mexitek\PHPColors\Color as ColorManipulationClass;
use Waka\Cloudis\Classes\YamlParserRelation;

trait CloudiTrait
{
    /**
     * return system file if new 
     */
    public function checkFileChange() {
        $newVersion = $this->deferredSrc;
        // si la nouvelle version n'existe plus et il existe une ancienne version on detruit l'image sur cloudi
        if(!$newVersion && $this->{$this->imgCloudi}) {
            $this->clouderDelete();
            
        }
        //si il y a une ancienne version
        if($this->{$this->imgCloudi}) {
            $oldVersion = $this->{$this->imgCloudi};
            if($oldVersion->created_at != $newVersion->created_at ) {
                //remplacement d'image
                $this->clouderDelete();
                $this->clouderUpload($newVersion);
                $this->createRelation();
            } else {
                return false;
            }
        } else {
            //Nouvelle image
            $this->clouderUpload($newVersion);
            $this->createRelation();
        }
    }
    /**
     * $delete sur cloudder
     */
    public function clouderDelete() 
    {
        $options = [
            "invalidate" => true
        ];
        Cloudder::destroy($this->cloudiIdOld, $options);
    }
    /**
     * $charge sur cloudder
     */
    public function clouderUpload($file) 
    {
        Cloudder::upload($file->getLocalPath(), $this->cloudiId);
    }
    public function getCloudiUrl() 
    {
        $targetModel = new $this->data_source->modelClass;
        $targetModelId = $targetModel->first()->id;
        $parser = new YamlParserRelation();
        //
        $options = $parser->parse($this->options, $targetModelId, $targetModel);
        //
        return Cloudder::secureShow($this->cloudiId, $options);
    }
    public function getCloudiRowUrl($width=30) 
    {
        $options =  [
            "gravity"=>"face",
            "width"=>$width,
            "crop"=>"thumb",
            "format"=>"jpg",
        ];
        return Cloudder::secureShow($this->cloudiId, $options);
    }
    public function getCloudiBase() 
    {
        return Cloudder::secureShow($this->cloudiId);
    }

    /**
     * $field nom du champs qui portera le nom
     */
    public function getCloudiIdAttribute() 
    {
        $srcPath = env('CLOUDINARY_PATH');
        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->slugAttribute];
        $timeStamp = str_slug($this->deferredSrc->created_at);
        return $srcPath.'/'.$modelName.'/'.$modelSlug.'v'.$timeStamp;
    }
    public function getCloudiIdOldAttribute() 
    {
        $srcPath = env('CLOUDINARY_PATH');
        $modelName = snake_case((new \ReflectionClass($this))->getShortName());
        $modelSlug = $this->attributes[$this->slugAttribute];
        $timeStamp = str_slug($this->{$this->imgCloudi}->created_at);
        return $srcPath.'/'.$modelName.'/'.$modelSlug.'v'.$timeStamp;
    }

    /**
     *  Retourne l'attribut imgCloudi en deffered
     */
    public function getDeferredSrcAttribute() {
        return $this->{$this->imgCloudi}()->withDeferred($this->sessionKey)->first();
    }

    /**
     * Image existe
     */
    public function getCloudiExisteAttribute() {
        if(!$this->{$this->imgCloudi}) return false;
        $url = $this->getCloudiBase();
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
    public function createRelation() {
        $targetModel = new $this->data_source->modelClass;
        $models = $targetModel->get();
        foreach($models as $model) {
            $model->montages()->attach($this->id);
        }

    }

}