<?php namespace Waka\Cloudis\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * ImagesList Form Widget
 */
class ImagesList extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'waka_cloudi_images_list';

    public $jsonValues;

    /**
     * @inheritDoc
     */
    public function init()
    {
    }

    public $functionClass;

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('imageslist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $noImage = true;
        $imagesList = $this->model->data_source->getAllPicturesKey();
        if ($imagesList) {
            $noImage = false;
        }
        $this->vars['noImage'] = $noImage;
        $this->vars['name'] = $this->formField->getName();
        $this->vars['values'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;

    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return \Backend\Classes\FormField::NO_SAVE_DATA;
    }

    public function onShowImages()
    {
        //liste des images de la classe depuis le datasource
        $imageWidget = $this->createFormWidget();
        $imageWidget->getField('source')->options = $this->model->data_source->getAllPicturesKey();
        $this->vars['imageWidget'] = $imageWidget;
        return $this->makePartial('popup');

    }

    public function onCreateImageValidation()
    {
        //mis d'en une collection des données existantes
        $data = [];
        $modelImagesValues = $this->getLoadValue();
        if ($modelImagesValues && count($modelImagesValues)) {
            $datas = new \October\Rain\Support\Collection($modelImagesValues);
        } else {
            $datas = new \October\Rain\Support\Collection();
        }
        //preparatio de l'array a ajouter
        $imageOptionsArray = post('imageOptions_array');

        $imageInfo = $this->model->data_source->getOnePictureKey($imageOptionsArray['source']);
        $imageOptionsArray = array_merge($imageOptionsArray, $imageInfo);

        $datas->push($imageOptionsArray);

        //enregistrement du model
        $field = $this->fieldName;
        $this->model[$field] = $datas;
        $this->model->save();

        //rafraichissement de la liste
        return [
            '#listimagesoptions' => $this->makePartial('listimagesoptions', ['values' => $datas]),
        ];
    }
    public function onUpdateImage()
    {

        $code = post('code');
        $source = post('source');

        $modelValues = $this->getLoadValue();
        //  trace_log($modelValues);
        $datas = new \October\Rain\Support\Collection($modelValues);
        $data = $datas->where('code', $code)->first();

        $imageWidget = $this->createFormWidget();
        $imageWidget->getField('source')->options = $this->model->data_source->getAllPicturesKey();
        $imageWidget->getField('code')->value = $data['code'];
        $imageWidget->getField('source')->value = $data['source'];
        $imageWidget->getField('width')->value = $data['width'];
        $imageWidget->getField('height')->value = $data['height'];
        $imageWidget->getField('crop')->value = $data['crop'];
        $this->vars['imageWidget'] = $imageWidget;
        $this->vars['oldCode'] = $code;
        $this->vars['oldSource'] = $source;
        return $this->makePartial('popup_update');

    }
    public function onDeleteImage()
    {

        $collectionCode = post('code');
        $datas = $this->getLoadValue();

        $updatedDatas = [];
        foreach ($datas as $key => $data) {
            if ($data['code'] != $collectionCode) {
                $updatedDatas[$key] = $data;
            }
        }

        //enregistrement du model
        $field = $this->fieldName;
        $this->model[$field] = $updatedDatas;
        $this->model->save();

        return [
            '#listimagesoptions' => $this->makePartial('listimagesoptions', ['values' => $updatedDatas]),
        ];

    }
    public function onUpdateImageValidation()
    {
        //On range collection code hidden das oldCollectionCode au cas ou le user change le collectionCode qui est notre clé
        $OldCollectionCode = post('collectionCode');
        $functionCode = post('functionCode');

        //mis d'en une collection des données existantes
        $datas = $this->getLoadValue();

        //preparatio de l'array a ajouter
        $widgetArray = post('attributes_array');
        //ajout du code qui n'est pas dans le widget_array
        $widgetArray['functionCode'] = post('functionCode');

        foreach ($datas as $key => $data) {
            if ($data['code'] == $OldCollectionCode) {
                $datas[$key] = $widgetArray;
            }
        }

        //enregistrement du model
        $field = $this->fieldName;
        $this->model[$field] = $datas;
        $this->model->save();

        //rafraichissement de la liste
        return [
            '#listimagesoptions' => $this->makePartial('listimagesoptions', ['values' => $datas]),
        ];
    }

    public function createFormWidget()
    {
        $config = $this->makeConfig('$/waka/cloudis/models/imageList/fields.yaml');
        $config->alias = 'imageOptionsWidget';
        $config->arrayName = 'imageOptions_array';
        $config->model = new \Waka\Cloudis\Models\ImageList();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}
