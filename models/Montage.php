<?php namespace Waka\Cloudis\Models;

use Model;
use Waka\Cloudis\Classes\YamlParserRelation;
use Waka\utils\Classes\DataSource;
use \Waka\Cloudis\Models\Settings as CloudisSettings;

/**
 * Montage Model
 */
class Montage extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\NestedTree;
    use \Winter\Storm\Database\Traits\SoftDelete;
    use \Waka\Cloudis\Classes\Traits\CloudiTrait;
    //

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_cloudis_montages';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:waka_cloudis_montages',
        'data_source' => 'required',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = ['options'];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [
        'src' => 'Waka\Cloudis\Models\CloudiFile',
        'masque' => 'Waka\Cloudis\Models\CloudiFile',
    ];
    public $attachMany = [];

    /**
     * Event
     */
    public function afterUpdate()
    {
        if ($this->auto_create) {
            $this->updateCLoudiRelationsFromMontage();
        }
    }
    /**
     * Attributes
     */
    public function getCloudi()
    {
        return $this->src();
    }

    public function filterFields($fields, $context = null)
    {
        $user = \BackendAuth::getUser();
        if (!$user->hasAccess('waka.cloudis.admin.*')) {
            $fields->options->hidden = true;
            $fields->data_source->readOnly = true;
            $fields->slug->readOnly = true;
            $fields->use_files->readOnly = true;
        }
    }

    /***
     * LISTs
     */
    public function listDataSource()
    {
        return \Waka\Utils\Classes\DataSourceList::lists();
    }

    public function updateCLoudiRelationsFromMontage()
    {
        //trace_log("updateCLoudiRelationsFromMontage : " . $this->active);
        if ($this->active) {
            $this->updateCloudiRelations('attach');
        } else {
            $this->updateCloudiRelations('detach');
        }
    }

    public function updateCloudiRelations($attachOrDetach = 'attach')
    {
        //trace_log('updateCloudiRelations : ');
        $mainClass = get_class($this);
        $ds = new DataSource($this->data_source);
        $models = $ds->class::get();
        foreach ($models as $model) {
            $parser = new YamlParserRelation($this, $model);
            // trace_log($model->name . " : " . $parser->errors . " , " . $attachOrDetach);
            $errors = $parser->errors ? true : false;
            // trace_log($errors);
            $this->attachOrDetach($model, $this->id, $attachOrDetach, $errors);
        }
    }
    public function attachOrDetach($model, $montageId, $attachOrDetach, $errors)
    {
        //trace_log('attachOrDetach : '.$attachOrDetach);
        if ($attachOrDetach == 'attach') {
            if (!$model->montages()->find($montageId)) {
                $model->montages()->attach($montageId, ['errors' => $errors]);
            } else {
                $model->montages()->updateExistingPivot($montageId, ['errors' => $errors]);
            }
        }
        if ($attachOrDetach == 'detach') {
            if ($model->montages()->find($montageId)) {
                $model->montages()->detach($montageId);
            }
        }
    }

    public function getUrl($id = null, $version = null)
    {
        $modelMontage = $this;
        $ds = new DataSource($this->data_source);
        $model = $ds->getModel($id);
        $parser = new YamlParserRelation($modelMontage, $model);

        //trace_log($parser->options);

        if (!$parser->options) {
            return $this->getUrlErrorImage();
        }

        $options = $parser->options;
        $formatOption = $version ? $this->setFormat($version) : null;

        //trace_log($formatOption);
        // si il y a un format particulier on le merge avec
        if ($formatOption) {
            array_push($options['transformation'], $formatOption);
        }

        //trace_log($options);

        return \Cloudder::secureShow($parser->src, $options);
    }

    public function getUrlErrorImage()
    {
        $cloudiSettings = CloudisSettings::instance();
        return $cloudiSettings->unknown->getUrl();
    }

    /**
     *
     */
}
