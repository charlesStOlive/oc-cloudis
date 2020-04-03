<?php namespace Waka\Cloudis\Classes;

use Yaml;

class YamlParserRelation
{
    use \Waka\Utils\Classes\Traits\StringRelation;
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
        $this->src = $this->getParsedValue($array['src']);
        $this->options = $this->recursiveSearch($array['options']);
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
        $result;
        return $this->getStringRelation($this->model, $value);
    }

    private function getLayer($value)
    {
        $layer = $this->getStringRelation($this->modelMontage, $value);
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
        //trace_log($value);
        //trace_log($this->model->name);
        $layer = $this->getStringRelation($this->model, $value);
        if ($layer) {
            $layer = $layer->cloudiId;
        } else {
            $this->errors++;
            $layer = $this->model->getErrorImage();
        }
        return $layer;
    }
}
