<?php namespace Waka\Cloudis\Models;

use Model;

/**
 * Montage Model
 */
class Montage extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;
    use \October\Rain\Database\Traits\SoftDelete;
    //
    use \Waka\Cloudis\Classes\Traits\CloudiTrait;
    public $cloudiSlug = 'slug';
    public $cloudiImages = ['src', 'masque'];

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
    protected $hidden = [];

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
    public $belongsTo = [
        'data_source' => 'Waka\utils\Models\DataSource',
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'cloudis_files' => [
            'Waka\Cloudis\Models\CloudisFile',
            'name' => 'cloudeable',
        ],
    ];
    public $attachOne = [
        'src' => 'System\Models\File',
        'masque' => 'System\Models\File',
    ];
    public $attachMany = [];

    /**
     * Event
     */
    public function afterSave()
    {
        // cet fonction utilse le trait cloudis
        $this->checkMontageChanges();
        $this->updateCLoudiRelationsFromMontage();
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
}
