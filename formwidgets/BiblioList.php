<?php namespace Waka\Cloudis\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Waka\Cloudis\Models\Biblio;

/**
 * wakalist Form Widget
 */
class BiblioList extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'waka_utils_wakaList';

    public $mode = "image";

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->fillFromConfig([
            'mode',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('wakalist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['model'] = $this->model;
        $this->formField->options = $this->getListOptions();
        $this->vars['field'] = $this->formField;
        $this->vars['value'] = $this->getLoadValue();
    }

    public function getListOptions()
    {
        return Biblio::where('type', $this->mode)->lists('name', 'slug');
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/workflow.css', 'waka.utils');
        $this->addJs('js/workflow.js', 'waka.utils');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }

}
