<?php namespace Waka\Cloudis\WakaRules\Asks;

use Waka\Utils\Classes\Rules\AskBase;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ApplicationException;
use Waka\Utils\Interfaces\Ask as AskInterface;

class MontageCloudi extends AskBase implements AskInterface
{
    /**
     * Returns information about this event, including name and description.
     */
    public function subFormDetails()
    {
        return [
            'name'        => 'Un montage cloudi',
            'description' => 'Un montage photo éditable',
            'icon'        => 'wicon-folder-images',
            'outputs' => [
                'word_type' => 'IMG',
            ]
        ];
    }

    public function defineValidationRules()
    {
        return [
            'srcImage' => 'required',
            'image' => 'required',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
        ];
    }

    public function getText()
    {
        $hostObj = $this->host;
        $idMontage = $hostObj->config_data['image'] ?? null;
        $name = \Waka\Cloudis\Models\Montage::find($idMontage)->name ?? "En attente";
        $src = $hostObj->config_data['srcImage'] ?? null;
        if($name) {
            return "montage cloudi : ".$name. " | "."source : ".$src;
        }
        return parent::getText();

    }

    public function listSelfParent()
    {
        $src = $this->getDs();
        if($src) {
            return $src->getSrcImage();
        }
        return [];
    }

    public function listCloudiMontage()
    {
        $src = $this->getDs();
        if(!$src) {
            return [];
        }
        $code = $this->host->srcImage ?? $this->getDs()->code;
        $src = $this->getDs()->getImagesFilesFromMontage($code);
        return $src;
    }
    public function listCropMode()
    {
        $config =  \Config::get('waka.cloudis::ImageOptions.crop.options');
        //trace_log($config);
        return $config;
        
    }
    public function listGravity()
    {
        $config =  \Config::get('waka.cloudis::ImageOptions.gravity.options');
        //trace_log($config);
        return $config;
        
    }

    public function resolve($modelSrc, $context = 'twig', $dataForTwig = []) {
        $clientModel = $modelSrc;
        //$clientModel = $this->getClientModel($clientId);
        $finalModel = null;
        //get configuration
        $configs = $this->host->config_data;
        $keyImage = $configs['image'] ?? null;
        $src = $configs['srcImage'] ?? null;
        $width = $configs['width'] ?? null;
        $height = $configs['height'] ?? null;
        $quality = $configs['quality'] ?? 1;
        $gravity = $configs['gravity'] ?? 'center';
        
        $imgWidth = round($width *   floatval($quality));
        $imgHeight =  round($height *   floatval($quality));

        $crop = $configs['crop'] ?? 'exact';
        
        //creation de la donnés
        if($src != $this->getDs()->code) {
            $finalModel = $clientModel->{$src};
        } else {
            $finalModel = $clientModel;
        }

        $options = [
                'width' => $imgWidth ?? null,
                'height' => $imgHeight ?? null,
                'crop' => $crop ?? null,
                'gravity' => $gravity ?? null,
            ];
        $montage = \Waka\Cloudis\Models\Montage::find($keyImage);
        //trace_log($finalModel->name);
        if($context == 'twig' ) {
            return [
                'path' => $finalModel->getMontage($montage, $options),
                'width' => $width . 'px',
                'height' => $height . 'px',
            ];
        } else {
            return [
                'path' => $finalModel->getMontage($montage, $options),
                'width' => $width . 'px',
                'height' => $height . 'px',
                'ratio' => true,
            ];
        }
    }
}
