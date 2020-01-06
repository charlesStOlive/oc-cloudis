<?php namespace Waka\Cloudis\Classes;
use Yaml;

Class YamlParserRelation {
    private $id;
    private $model;
    private $modelMontage;

    function __construct() {
    }

    public function parse($modelMontage, $id, $model) {
        $this->id = $id;
        $this->model = $model;
        $this->modelMontage = $modelMontage;

        $array = Yaml::parse($modelMontage->options);
        return $this->recursiveSearch($array['options']);

    }

    private function recursiveSearch(array $array) {
        $returnArray = array();
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $returnArray[$key] = $this->recursiveSearch($value);
            } else {
                //check if their is some text between xx- & -xx
                if(preg_match("/xx-(.*?)-xx/", $value, $match)) {
                    //on lance la fonction de recherche ( la fonction utilise le model lié)
                    $replace =  $this->getModel($match[1]);
                    $replacement =  preg_replace("/xx-(.*)-xx/",$replace,$value);
                    $returnArray[$key] = $replacement;
                }
                elseif(preg_match("/xl-(.*?)-lx/", $value, $match)) {
                    //on lance la fonction de recherche ( la fonction utilise le model lié)
                    $replace =  $this->getLayer($match[1]);
                    $replacement =  preg_replace("/xl-(.*)-lx/",$replace,$value);
                    $returnArray[$key] = $replacement;
                }
                elseif(preg_match("/xlr-(.*?)-rlx/", $value, $match)) {
                    //on lance la fonction de recherche ( la fonction utilise le model lié)
                    $replace =  $this->getModelLayer($match[1]);
                    $replacement =  preg_replace("/xlr-(.*)-rlx/",$replace,$value);
                    $returnArray[$key] = $replacement;
                }  
                else {
                    $returnArray[$key] = $value;
                }
            }
        }
        return $returnArray;
    }

    private function getModel($value) {
        $array = explode(".", $value);
        trace_log(count($array));
        if(count($array)>1) {
            $relation = [];

            return $this->model::find($this->id)[$array[0]][$array[1]]; 
        }
        return $this->model::find($this->id)[$value];
    }
    private function getLayer($value) {
        return str_replace('/', ':', $this->modelMontage->getCloudiId($value));
    }
    private function getModelLayer($value) {
        return str_replace('/', ':', $this->model::find($this->id)->getCloudiId($value));
    }
    public function getUrl() {
        return null;
    }
}