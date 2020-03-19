<?php namespace Waka\Cloudis\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    // A unique code
    public $settingsCode = 'waka_cloudis_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';

    public function beforeSave()
    {
        if ($this::get('unknown') != $this::get('previous_file')) {
            $srcPath = $this::get('cloudinary_path') . '/errors/unkown';
            $cloudData = \Cloudder::upload(storage_path('app/media/' . $this::get('unknown')), $srcPath);
            $result = $cloudData->getResult();
            $version = $result['version'];
            $this::set([
                'version' => $version,
                'previous_file' => $this::get('unknown'),
                'srcPath' => 'v' . $version . '/' . $srcPath,
            ]);
        }
    }

    public function getSettingsUrl()
    {
        // $modelMontage = $this;
        // $model = new $this->data_source->modelClass;
        // $modelId;
        // if ($id) {
        //     $modelId = $id;
        // } else {
        //     $modelId = $this->data_source->test_id;
        // }
        // $model = $model::find($modelId);
        // $parser = new YamlParserRelation($modelMontage, $model);
        // $options = $parser->options;
        // $formatOption = $version ? $this->setFormat($version) : null;
        // // si il y a un format particulier on le merge avec
        // if ($formatOption) {
        //     array_push($options['transformation'], $formatOption);
        // }

        // return Cloudder::secureShow($parser->src, $options);

    }

    public function checkIfExiste()
    {
        // trace_log("test de cloudiExiste");
        // $url = $this->getCloudiRowUrl($src);
        // $handle = curl_init($url);
        // curl_setopt($handle, CURLOPT_NOBODY, true);
        // //  Get the HTML or whatever is linked in $url.
        // $response = curl_exec($handle);
        // if(curl_getinfo($handle, CURLINFO_HTTP_CODE) == "200") {
        //     curl_close($handle);
        //     return true;
        // } else {
        //     curl_close($handle);
        //     return false;
        // }
    }

}
