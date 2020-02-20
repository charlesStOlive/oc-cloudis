<?php namespace Waka\Cloudis\Classes\Traits;

use ApplicationException;
use Yaml;

trait CloudisKey
{
    public function encryptKeyedImage($dataSource, $id = null)
    {
        $targetModel;
        if (!$id) {
            $targetModel = $dataSource->test_id;
        } else {
            $targetModel = $dataSource->modelClass::find($id);
        }
        $collection = [];
        $datas = Yaml::parse($dataSource->media_files);
        foreach ($datas as $key => $data) {
            $tempModel = $targetModel;
            $startKey = 'from=null';
            if (array_key_exists('from', $data)) {
                if (!$targetModel[$data['from']]) {
                    throw new ApplicationException('dataSource model relation not exist : ' . $data['from']);
                }

                // nous sommes dans une relation.
                $tempModel = $targetModel[$data['from']];
                $startKey = 'from=' . $data['from'];
            }
            if (!$data['type']) {
                throw new ApplicationException('dataSource type missing');
            }

            $startKey .= '**type=' . $data['type'];
            //
            switch ($data['type']) {
                case 'file':
                    $key = $startKey . '**key=' . $key;
                    $collection[$key] = $data['label'];
                    break;
                case 'media':
                    $key = $startKey . '**key=' . $key;
                    $collection[$key] = $data['label'];
                    break;
                case 'montages':
                    foreach ($tempModel->montages as $montage) {
                        $key = $startKey . '**key=' . $montage->slug;
                        $collection[$key] = $data['label'] . ' : ' . $montage->name;
                    }
                    break;
            }

        }
        return $collection;
    }
    /**
     * Remove :w:h from word key if exist
     */
    public function getWordImageKey($tag)
    {
        $parts = explode(':', $tag);
        return $parts[0];
    }
    public function cleanWordKey($key)
    {
        $parts = explode('.', $key);
        return $parts[1];
    }
    public function decryptKeyedImage($key, $model)
    {
        //trace_log("************DECRIPTKEY******************");
        //trace_log($key);
        $parts = explode('**', $key);
        $fromPart = array_shift($parts);
        $typePart = array_shift($parts);
        $keyPart = array_shift($parts);
        $from = explode('=', $fromPart);
        $type = explode('=', $typePart);
        $key = explode('=', $keyPart);
        //trace_log($from[1]);
        //trace_log($type[1]);
        //trace_log($key[1]);
        $url;
        $tempModel = $model;
        //trace_log('Before decrypt key from ' . $tempModel->name);
        if ($from[1] != 'null') {
            //trace_log("relation = " . $from[1]);
            $tempModel = $model[$from[1]];
        }
        //trace_log('After decrypt key from ' . $tempModel->name);
        switch ($type[1]) {
            case 'file':
                $url = $tempModel[$key[1]]->getPath();
                break;
            case 'media':
                $url = storage_path('app/media/' . $tempModel[$key[1]]);
                break;
            case 'montages':
                //trace_log("montages");
                //trace_log($tempModel->montages->toArray());
                $montage = $tempModel->montages->where('slug', $key[1])->first();
                if ($montage) {
                    $url = $montage->getCloudiUrl('src', $tempModel->id);
                } else {
                    $url = null;
                }

                //trace_log($url);
                break;
        }
        return $url;
    }

}
