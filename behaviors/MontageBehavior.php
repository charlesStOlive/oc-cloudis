<?php namespace Waka\Cloudis\Behaviors;

use Backend\Classes\ControllerBehavior;
use Waka\Cloudis\Models\Montage;

class MontageBehavior extends ControllerBehavior
{
    public $model;
    public $errors;

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->model = $controller->formGetModel();
        $this->errors = [];
        \Event::listen('waka.utils::conditions.error', function ($error) {
            array_push($this->errors, $error);
        });
    
    }

    public function onLoadMontageBehaviorPopupForm()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');

        $ds = \DataSources::findByClass($modelClass);
        $options = $ds->getProductorOptions('Waka\Cloudis\Models\Montage', $modelId);

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['errors'] = $this->errors;
        $this->vars['modelClass'] = $modelClass;

        return $this->makePartial('$/waka/cloudis/behaviors/montagebehavior/_popup.htm');
    }

    /**
     * Popup dans barre d'outil
     */
    public function onLoadMontageBehaviorContentForm()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');

        $ds = \DataSources::findByClass($modelClass);
        trace_log('ok');
        $options = $ds->getProductorOptions('Waka\Cloudis\Models\Montage', $modelId);
        trace_log('dddssd');

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['errors'] = $this->errors;
        $this->vars['modelClass'] = $modelClass;

        return ['#popupActionContent' => $this->makePartial('$/waka/cloudis/behaviors/montagebehavior/_content.htm')];
    }

    public function onSelectMontage() {
        $productorId = post('productorId');
        $modelClass = post('modelClass');
        $modelId = post('modelId');
        
        $montage = Montage::find($productorId);
        $this->vars['montage_url'] = $modelClass::find($modelId)->getMontage($montage);

        return [
            '#montage_result' => $this->makePartial('$/waka/cloudis/behaviors/montagebehavior/_link_image.htm')
        ];
    }

    public function onLoadMontageTestForm() {
        $productorId = \Input::get('productorId');
        $montage = Montage::find($productorId);
        $ds = \DataSources::find($montage->waka_session->data_source);
        $model = $ds->class::find($montage->waka_session->ds_id_test);
        if(!$model) {
            throw new \ValidationException(['test_id' => 'Le champs de test est vide']);
        }
        $this->vars['montage_url'] =  $model->getMontage($montage);
        return $this->makePartial('$/waka/cloudis/behaviors/montagebehavior/_popup_test.htm');
    }
}
