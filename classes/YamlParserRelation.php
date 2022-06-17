<?php namespace Waka\Cloudis\Classes;

use Yaml;

class YamlParserRelation
{
    private $id;
    private $model;
    private $modelMontage;
    public $errors;

    public $src;
    public $options;

    public function __construct($modelMontage, $model)
    {
        $this->errors = 0;
        $this->model = $model;
        $this->modelMontage = $modelMontage;
        $array = Yaml::parse($modelMontage->options);
        if ($array['src'] ?? false) {
            $this->src = $this->getParsedValue($array['src']);
        } else {
            $this->errors = 1;
        }
        if ($array['options'] ?? false) {
            $this->options = $this->recursiveSearch($array['options']);
        } else {
            $this->errors = 1;
        }
    }

    private function recursiveSearch(array $array)
    {
        $returnArray = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $returnArray[$key] = $this->recursiveSearch($value);
            } else {
                //check if their is some text between xx- & -xx
                // si il y a une url avec des / on modifie / par : pour cloudi
                $returnArray[$key] = str_replace('/', ':', $this->getParsedValue($value));
            }
        }
        return $returnArray;
    }

    public function getParsedValue($value)
    {
        if (preg_match("/xx-(.*?)-xx/", $value, $match)) {
            //on lance la fonction de recherche ( la fonction utilise le model lié)
            $replace = $this->getModel($match[1]);
            if (!$replace) {
                $this->errors++;
            }

            $replacement = preg_replace("/xx-(.*)-xx/", $replace, $value);
            return $replacement;
        } elseif (preg_match("/xl-(.*?)-lx/", $value, $match)) {
            //on lance la fonction de recherche ( la fonction utilise le model lié)
            $replace = $this->getLayer($match[1]);

            $replacement = preg_replace("/xl-(.*)-lx/", $replace, $value);
            return $replacement;
        } elseif (preg_match("/xlr-(.*?)-rlx/", $value, $match)) {
            //on lance la fonction de recherche ( la fonction utilise le model lié)
            $replace = $this->getModelLayer($match[1]);
            $replacement = preg_replace("/xlr-(.*)-rlx/", $replace, $value);

            return $replacement;
        } else {
            return $value;
        }
    }

    private function getModel($value)
    {
        //trace_log('get model');
        return array_get($this->model, $value);
    }

    private function getLayer($value)
    {
        //trace_log('get layer');
        $layer = array_get($this->modelMontage, $value);
        if ($layer) {
            $layer = $layer->cloudiId;
        } else {
            $this->errors++;
            $layer = $this->modelMontage->getErrorImage();
        }
        return $layer;
    }
    private function getModelLayer($value)
    {
        // trace_log($value);
        // trace_log($this->model->name);
        $layer = array_get($this->model, $value);
        // trace_log($layer);
        if ($layer) {
            $layer = $layer->cloudiId;
        } else {
            $this->errors++;
            $layer = $this->model->getErrorImage();
        }
        return $layer;
    }
}
