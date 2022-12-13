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
 * Settings Model.
 *
 * @property string $key
 * @property string $value
 * @property bool   $cached
 */
class Setting extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'settings';

    /**
     * {@inheritdoc}
     *
     * @var string[]
     */
    protected $casts = [
        'cached' => 'boolean',
    ];

    /**
     * @var string[] The fields of the table for the current model.
     */
    protected $fillable = [
        'key',
        'value',
        'cached',
    ];

    /**
     * @var bool Enable timestamps for Users.
     */
    public $timestamps = true;
}
