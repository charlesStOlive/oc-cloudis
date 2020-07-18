<?php namespace Waka\Cloudis\Classes;

use October\Rain\Support\Collection;

class GroupedImages
{
    use \Waka\Utils\Classes\Traits\StringRelation;

    public $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function getAll($dataSource)
    {
        return $this->getAllDataSourceImage($dataSource);
    }

    public function getModelImages()
    {
        return $this->getCloudisList($this->model);
    }
    public function getModelMonntages()
    {
        return $this->getCloudiMontagesList($this->model);
    }
    public function getLists($dataSource)
    {
        $collection = $this->getAllDataSourceImage($dataSource);
        if ($collection) {
            return $collection->lists('name', 'key');
        } else {
            return null;
        }

    }
    public function getOne($dataSource, $key)
    {
        $collection = $this->getAllDataSourceImage($dataSource);
        return $collection->where('key', $key)->first();
    }

    public function getCloudisList($model, $relation = null)
    {
        $modelClassName = get_class($model);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];
        if (!$relation) {
            $relation = 'self';
        }

        $cloudiImgs = $model->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                $img = [
                    'field' => $key,
                    'type' => 'cloudi',
                    'relation' => $relation,
                    'key' => $shortName . $key,
                    'name' => $shortName . ' : ' . $key,
                ];
                array_push($cloudiKeys, $img);
            }
        }
        return $cloudiKeys;
    }
    public function getCloudiMontagesList($model, $relation = null)
    {
        $modelClassName = get_class($model);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];
        if (!$relation) {
            $relation = 'self';
        }

        $montages = $model->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $img = [
                    'id' => $montage->id,
                    'type' => 'montage',
                    'relation' => $relation,
                    'key' => $shortName . $montage->id,
                    'name' => 'Montage : ' . $montage->name,
                ];
                array_push($cloudiKeys, $img);
            }
        }
        return $cloudiKeys;
    }

    private function getAllDataSourceImage($dataSource)
    {
        $allImages = new Collection();

        $listsImages = $this->getCloudisList($this->model);
        $listMontages = $this->getCloudiMontagesList($this->model);
        if ($listsImages) {
            $allImages = $allImages->merge($listsImages);
        }
        if ($listMontages) {
            $allImages = $allImages->merge($listMontages);
        }
        $relationWithImages = new Collection($dataSource->relations_list);
        if ($relationWithImages->count()) {
            $relationWithImages = $relationWithImages->where('has_images', true)->pluck('name');
            foreach ($relationWithImages as $relation) {
                $subModel = $this->getStringModelRelation($this->model, $relation);
                $listsImages = $this->getCloudisList($subModel, $relation);
                $listMontages = $this->getCloudiMontagesList($subModel, $relation);
                if ($listsImages) {
                    $allImages = $allImages->merge($listsImages);
                }
                if ($listMontages) {
                    $allImages = $allImages->merge($listMontages);
                }

            }
        }

        // c'est pas encore au point tt ça...

        // $indeClassWithImages = new Collection($dataSource->inde_class_list);
        // if ($indeClassWithImages->count()) {
        //     $indeClassWithImages = $indeClassWithImages->where('has_images', true);
        //     foreach ($indeClassWithImages as $indeClass) {
        //         //trace_log($indeClass);
        //         $class = new $indeClass['class'];
        //         $class = $class::find($indeClass['id'])->first();
        //         $listsImages = $this->getCloudisList($class);
        //         //$listMontages = $this->getCloudiMontagesList($class);
        //         if ($listsImages) {
        //             $allImages = $allImages->merge($listsImages);
        //         }
        //         // if ($listMontages) {
        //         //     $allImages = $allImages->merge($listMontages);
        //         // }
        //     }
        // }

        return $allImages;
    }

    private function createObject($value, $relation, $submodel)
    {

    }
}
