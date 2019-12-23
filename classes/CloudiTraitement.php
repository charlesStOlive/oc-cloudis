<?php namespace Waka\Cloudis\Classes;

Class YamlParserRelation {
    private $id;
    private $model;

    function __construct($yamlString, $id, $model) {
        $this->id = $id;
        $this->model = $model;
        $array = Yaml::parse($yamlString);
        $this->recursiveSearch($array['options']);
    }

    private function recursiveSearch(array $array) {
        $returnArray = array();
        foreach($array as $key => $value) {
            if(is_array($value)) {
                $returnArray[$key] = $this->recursiveSearch($value);
            } else {
                if($key == 'valueFrom') {
                    $value =  $this->getModel($value);
                    $returnArray[$key] = $value;
                } else {
                    $returnArray[$key] = $value;
                }
            }
        }
        return $returnArray();
    }
    private function getModel($value) {
        trace_log($this->model::find($this->id)[$value]);
        return $this->model::find($this->id)[$value];

    }
    public function getUrl() {
        return null;
    }
}