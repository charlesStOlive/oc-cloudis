<?php namespace Waka\Cloudis\Behaviors;

use Backend\Classes\ControllerBehavior;

class PopupCloudis extends ControllerBehavior
{
    public $model;

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->model = $controller->formGetModel();

    }

    //ci dessous tous les calculs pour permettre l'import excel.

    public function onCallPopupCloudis()
    {
        $modelName = post('model');
        $modelId = post('modelId');
        $model;
        $src;
        if ($modelName == 'Waka\Cloudis\Models\Montage') {
            $model = $modelName::find($modelId);
            $src = 'src';
        }
        $this->vars['url'] = $model->getCloudiUrl($src);
        return $this->makePartial('$/waka/cloudis/behaviors/popupcloudis/_popup.htm');
    }
    public function onCallPopupModelCloudis()
    {
        $model = post('model');
        $modelId = post('modelId');
        $relationId = post('relationId');

        $model = $model::find($modelId);
        $montage = $model->montages->find($relationId);

        $url = $model->getCloudiModelUrl($montage);
        $this->vars['url'] = $url;
        return $this->makePartial('$/waka/cloudis/behaviors/popupcloudis/_popup.htm');
    }
}
