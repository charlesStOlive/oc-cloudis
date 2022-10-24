<?php namespace Waka\Cloudis\Models;

use Model;
use Waka\Cloudis\Classes\YamlParserRelation;
use \Waka\Cloudis\Models\Settings as CloudisSettings;

/**
 * montage Model
 */

class Montage extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \Waka\Utils\Classes\Traits\DataSourceHelpers;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_cloudis_montages';


    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    //protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'state' => 'required',
        'name' => 'required',
        'slug' => 'required|unique',
    ];

    public $customMessages = [
        'data_source.required' => 'waka.cloudis::montage.e.data_source',
        'state.required' => 'waka.cloudis::montage.e.state',
    ];

    /**
     * @var array attributes send to datasource for creating document
     */
    public $attributesToDs = [
    ];


    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
    ];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [
    ];

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
    ];

/**
    * @var array Spécifié le type d'export à utiliser pour chaque champs
    */
    public $importExportConfig = [
    ]; 

    /**
     * @var array Relations
     */
    public $hasOne = [
    ];
    public $hasMany = [
    ];
    public $hasOneThrough = [
    ];
    public $hasManyThrough = [
    ];
    public $belongsTo = [
    ];
    public $belongsToMany = [
    ];        
    public $morphTo = [
    ];
    public $morphOne = [
        'waka_session' => [
            'Waka\Session\Models\WakaSession',
            'name' => 'sessioneable',
            'delete' => true
        ],
    ];
    public $morphMany = [
        'rule_conditions' => [
            'Waka\Utils\Models\RuleCondition',
            'name' => 'conditioneable',
            'delete' => true
        ],
    ];
    public $attachOne = [
        'src' => ['Waka\Cloudis\Models\CloudiFile'],
        'masque' => ['Waka\Cloudis\Models\CloudiFile'],
    ];
    public $attachMany = [
    ];

    //startKeep/

    /**
     *EVENTS
     **/

    /**
     * LISTS
     **/
    public function listStates() {
        return \Config::get('waka.utils::basic_state');
    }

    /**
     * GETTERS
     **/

    /**
     * SCOPES
     */
    public function scopeActive($query) {
        return $query->where('state', 'actif');

    }

    /**
     * SETTERS
     */
 
    /**
     * FILTER FIELDS
     */

    /**
     * OTHERS
     */

//endKeep/
}