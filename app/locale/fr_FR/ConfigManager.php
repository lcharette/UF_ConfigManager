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
            'TITLE'       => 'Mauvais schema',
            'DESCRIPTION' => 'Le schema de configuration est invalide ou les champs requis sont manquant.',
        ],
        'MISSING_DATA' => [
            'TITLE'       => 'Données manquantes',
            'DESCRIPTION' => 'Données POST sont manquant ou invalide',
        ],
        'SCHEMA_NOT_FOUND' => [
            'TITLE'       => 'Schema introuvable',
            'DESCRIPTION' => 'Schema {{schema}} introuvable',
        ],
    ],
    'CONFIG_MANAGER' => [
        'TITLE'       => 'Configuration du site',
        'DESCRIPTION' => 'Gérer la configuration du site',
        'SAVED'       => 'Changements sauvegardés avec succès !',
    ],
];
