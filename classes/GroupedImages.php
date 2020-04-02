<?php namespace Waka\Cloudis\Classes;

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
    public function getLists($dataSource)
    {
        $collection = $this->getAllDataSourceImage($dataSource);
        return $collection->lists('name', 'key');
    }
    public function getOne($dataSource, $key)
    {
        $collection = $this->getAllDataSourceImage($dataSource);
        return $collection->where('key', $key)->first();
    }

    private function getCloudisList($model)
    {
        $modelClassName = get_class($model);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];

        $cloudiImgs = $model->attachOne;
        foreach ($cloudiImgs as $key => $value) {
            if ($value == 'Waka\Cloudis\Models\CloudiFile') {
                $img = [
                    'field' => $key,
                    'type' => 'cloudi',
                    'className' => $modelClassName,
                    'key' => $shortName . $key,
                    'name' => $shortName . ' : ' . $key,
                ];
                array_push($cloudiKeys, $img);
            }
        }
        return $cloudiKeys;
    }
    private function getCloudiMontagesList($model)
    {
        $modelClassName = get_class($model);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiKeys = [];

        $montages = $model->montages;
        if ($montages) {
            foreach ($montages as $montage) {
                $img = [
                    'id' => $montage->id,
                    'type' => 'montage',
                    'className' => $modelClassName,
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
        $relationWithImages = new \October\Rain\Support\Collection($dataSource->relations_list);
        if (!$relationWithImages->count()) {
            return;
        }
        $relationWithImages = $relationWithImages->where('has_images', true)->pluck('name');

        $allImages = new \October\Rain\Support\Collection();

        $listsImages = $this->getCloudisList($this->model);
        $listMontages = $this->getCloudiMontagesList($this->model);
        if ($listsImages) {
            $allImages = $allImages->merge($listsImages);
        }
        if ($listMontages) {
            $allImages = $allImages->merge($listMontages);
        }

        foreach ($relationWithImages as $relation) {
            $subModel = $this->getStringModelRelation($this->model, $relation);
            $listsImages = $this->getCloudisList($subModel);
            $listMontages = $this->getCloudiMontagesList($subModel);
            if ($listsImages) {
                $allImages = $allImages->merge($listsImages);
            }
            if ($listMontages) {
                $allImages = $allImages->merge($listMontages);
            }

        }
        return $allImages;
    }

    private function createObject($value, $relation, $submodel)
    {

    }
}
