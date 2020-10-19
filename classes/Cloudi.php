<?php namespace Waka\Cloudis\Classes;

use October\Rain\Support\Collection;

class Cloudi
{

    public static function listCloudis($model, $relation = null)
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

    public static function listMontages($model, $relation = null)
    {
        $modelClassName = get_class($model);
        $shortName = (new \ReflectionClass($modelClassName))->getShortName();
        $cloudiMontages = [];
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
                array_push($cloudiMontages, $img);
            }
        }
        return $cloudiMontages;
    }
    

}