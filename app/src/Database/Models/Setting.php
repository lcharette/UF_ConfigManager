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
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Setting extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'settings';

    /**
     * {@inheritdoc}
     * @phpstan-ignore-next-line
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
}
