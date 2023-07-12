<?php namespace Waka\Cloudis\WakaRules\Contents;

use Waka\WakaBlocs\Classes\Rules\RuleContentBase;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ApplicationException;
use Waka\WakaBlocs\Interfaces\RuleContent as RuleContentInterface;

class ImageCloudi extends RuleContentBase implements RuleContentInterface
{
    protected $tableDefinitions = [];

    /**
     * Returns information about this event, including name and description.
     */
    public function subFormDetails()
    {
        return [
            'name'        => 'Une image cloudi',
            'description' => 'Une image venant de la bibliothèque cloudi',
            'icon'        => 'icon-picture',
            'premission'  => 'wcli.utils.cond.edit.admin',
        ];
    }

    public function getText()
    {
        //trace_log('getText HTMLASK---');
        $hostObj = $this->host;
        //trace_log($hostObj->config_data);
        $text = $hostObj->config_data['title'] ?? null;
        if($text) {
            return $text;
        }
        return parent::getText();

    }

    /**
     * IS true
     */

    public function resolve($ds = []) {


        return [
            'title' => $this->getConfig('title'),
            'html' => $this->getConfig('html'),
        ];
    }
}
