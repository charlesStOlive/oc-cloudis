<?php namespace Waka\Cloudis\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * MontagesList Form Widget
 */
class MontagesList extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'waka_cloudis_montages_list';

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('montageslist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['montages'] = $this->getMontageList();
    }

    public function getMontageList() {
        return $this->model->montages;
    }
    public function onRefreshList() {

        $this->vars['montages'] = $this->getMontageList();
        return [
            '#listMontage' => $this->makePartial('list')
        ];
        //return $this->makePartial('montageslist');
    }

    public function onShowCloudiImage() {
        $montage = $this->model->montages->find(post('id'));
        $url = $this->model->getCloudiModelUrl($montage);
        $this->vars['url'] = $url;
        return $this->makePartial('popup');
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        $this->addCss('css/montageslist.css', 'Waka.Cloudis');
        $this->addJs('js/montageslist.js', 'Waka.Cloudis');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return \Backend\Classes\FormField::NO_SAVE_DATA;
    }
}
