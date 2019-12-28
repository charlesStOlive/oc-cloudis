<?php namespace Waka\Cloudis\Behaviors;
use Waka\Cloudis\Models\Montage;

use Backend\Classes\ControllerBehavior;

use October\Rain\Support\Collection;
use October\Rain\Exception\ApplicationException;
use Flash;
use Redirect;
use Session;
use Lang;


class PopupCloudis extends ControllerBehavior
{


	public function __construct($controller)
    {
        parent::__construct($controller);
    }


     //ci dessous tous les calculs pour permettre l'import excel. 

    public function onCallPopupCloudis()
    {
        $modelName = post('model');
        $modelId = post('modelId');
        $model;
        if($modelName == 'Waka\Cloudis\Models\Montage')  {
            $model = $modelName::find($modelId);
        } 
        $this->vars['url'] = $model->getCloudiUrl();
        return $this->makePartial('$/waka/cloudis/behaviors/popupcloudis/_popup.htm');
    }    
}