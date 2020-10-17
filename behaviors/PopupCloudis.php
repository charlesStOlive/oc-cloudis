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

    public function onCallPopupCloudis()
    {
        $modelName = post('model');
        $modelId = post('modelId');
        $model;
        $src;
        if ($modelName == 'Waka\Cloudis\Models\Montage') {
            $model = $modelName::find($modelId);
        }
        $this->vars['url'] = $model->getUrl();
        //trace_log($model->getCloudiUrl());
        return $this->makePartial('$/waka/cloudis/behaviors/popupcloudis/_popup.htm');
    }
    public function onCallPopupModelCloudis()
    {
        $model = post('model');
        $modelId = post('modelId');
        $relationId = post('relationId');

        $model = $model::find($modelId);
        $montage = $model->montages->find($relationId);
        $montage->getUrl();

        $url = $model->getMontage($montage);
        //trace_log($montage);
        $this->vars['url'] = $url;
        return $this->makePartial('$/waka/cloudis/behaviors/popupcloudis/_popup.htm');
    }
}
