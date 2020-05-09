<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Settings class.
 *
 * @extends Model
 */
class Config extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'settings';

    /**
     * @var array The fields of the table for the current model.
     */
    protected $fillable = [
        'key',
        'value',
        'cache',
    ];

    /**
     * @var bool Enable timestamps for Users.
     */
    public $timestamps = true;

    /**
     * Create a new Project object.
     */
    public function __construct($properties = [])
    {
        parent::__construct($properties);
    }

    /**
     * Model's relations
     * Each of those should be in delete !
     */

    /**
     * Model's parent relation.
     */

    /**
     * Delete this group from the database, along with any linked user and authorization rules.
     */
    public function delete()
    {
        // Delete the main object
        $result = parent::delete();

        return $result;
    }
}
