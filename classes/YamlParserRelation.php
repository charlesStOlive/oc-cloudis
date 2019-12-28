<?php namespace Waka\Cloudis\Classes;
use Yaml;

Class YamlParserRelation {
    private $id;
    private $model;

    function __construct() {
    }

    public function parse($yamlString, $id, $model) {
        $this->id = $id;
        $this->model = $model;
        $array = Yaml::parse($yamlString);
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
                } else {
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

            return str_replace('/', ':', $this->model::find($this->id)[$array[0]][$array[1]]); 
        }
        return str_replace('/', ':', $this->model::find($this->id)[$value]);
    }
    public function getUrl() {
        return null;
    }
}