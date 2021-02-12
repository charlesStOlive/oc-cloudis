<?php namespace Waka\Cloudis\Models;

use Model;

/**
 * biblio Model
 */

class Biblio extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \Waka\Cloudis\Classes\Traits\CloudiTrait;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_cloudis_biblios';

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
        'slug' => 'required|unique:waka_cloudis_biblios',
    ];

    public $customMessages = [
        'name.required' => 'waka.cloudis::biblio.e.name',
        'slug.required' => 'waka.cloudis::biblio.e.slug',
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
        'options',
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
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [
    ];
    public $belongsTo = [
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [
    ];
    public $morphMany = [
    ];
    public $attachOne = [
        'src' => 'Waka\Cloudis\Models\CloudiFile',
        'srcv' => 'Waka\Cloudis\Models\CloudiFile',
    ];
    public $attachMany = [
    ];

    /**
     *EVENTS
     **/

    /**
     * LISTS
     **/
    public function listTypeImage()
    {
        return ['image' => 'Image', 'video' => 'Vidéo'];
    }

    /**
     * GETTERS
     **/

    /**
     * SCOPES
     */

    /**
     * SETTERS
     */

    /**
     * FILTER FIELDS
     */
    public function filterFields($fields, $context = null)
    {
        if (isset($fields->src)) {
            if ($this->type == 'image') {
                $fields->srcv->hidden = 'true';
            } else {
                $fields->src->hidden = 'true';
            }
        }
    }

    /**
     * OTHERS
     */

}
