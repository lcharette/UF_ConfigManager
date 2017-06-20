<?php
/**
 * UF Settings
 *
 * @link      https://github.com/lcharette/UF_Settings
 * @copyright Copyright (c) 2016 Louis Charette
 * @license
 */
namespace UserFrosting\Sprinkle\ConfigManager\Model;

use \Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Settings class.
 *
 * @extends UFModel
 */
class Config extends Model {

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "settings";

    /**
     * @var array The fields of the table for the current model.
     */
    protected $fillable = [
        "key",
        "value",
        "cache"
    ];

    /**
     * @var bool Enable timestamps for Users.
     */
    public $timestamps = true;

    /**
     * Create a new Project object.
     *
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
    }

    /**
     * Model's relations
     * Each of those should be in delete !
     *
     */

    /**
     * Model's parent relation
     *
     */

    /**
     * Delete this group from the database, along with any linked user and authorization rules
     *
     */
    public function delete()
    {
        // Delete the main object
        $result = parent::delete();
        return $result;
    }
}