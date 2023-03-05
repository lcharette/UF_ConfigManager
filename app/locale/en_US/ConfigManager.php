<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

return [
    'ERROR' => [
        'BAD_SCHEMA' => [
            'TITLE'       => 'Bad Schema',
            'DESCRIPTION' => 'Config Schema is invalid or is missing the required fields',
        ],
        'MISSING_DATA' => [
            'TITLE'       => 'Missing Data',
            'DESCRIPTION' => 'POST data is missing or invalid',
        ],
        'SCHEMA_NOT_FOUND' => [
            'TITLE'       => 'Schema not found',
            'DESCRIPTION' => 'Schema {{schema}} not found',
        ],
    ],
    'CONFIG_MANAGER' => [
        'TITLE'       => 'Site Configuration',
        'DESCRIPTION' => 'Manage your site configuration',
    ],
];
