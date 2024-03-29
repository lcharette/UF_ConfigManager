<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

return [
    'SITE'     => [
        'CONFIG'       => [
            '@TRANSLATION' => 'UserFrosting Settings',
            'DESC'         => 'Core settings of UserFrosting. See the config file for more configuration options',
        ],
        'TITLE'        => [
            '@TRANSLATION' => 'Site title',
            'REQUIRED'     => 'The site title is required',
        ],
        'REGISTRATION' => [
            'ENABLED'                    => 'Enabled site registration',
            'REQUIRE_EMAIL_VERIFICATION' => 'Require email verification when registering',
        ],
    ],
    'SETTINGS' => [
        'DISPLAY_ERROR_DETAILS' => 'Display error details',
    ],
];
