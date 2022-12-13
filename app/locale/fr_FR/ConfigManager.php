<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

return [
    'SITE' => [
        'CONFIG' => [
            '@TRANSLATION' => 'Paramètres de UserFrosting',

            'DESC' => "Paramètres principaux de UserFrosting. Voir le fichier config pour plus d'options",

            'MANAGER' => 'Gestionnaire des paramètres',

            'PAGEDESC' => 'Cette page permet de modifier les paramètres globaux du site enregistrés dans la base de données',

            'SAVED' => 'Changements sauvegardés avec succès !',
        ],
        'TITLE' => [
            '@TRANSLATION' => 'Titre du site',
            'REQUIRED'     => 'Le titre du site est requis',
        ],
        'REGISTRATION' => [
            'ENABLED'                    => "Activer l'inscription",
            'REQUIRE_EMAIL_VERIFICATION' => "Exiger une vérification par e-mail lors de l'inscription",
        ],
    ],
    'SETTINGS' => [
        'DISPLAY_ERROR_DETAILS' => 'Afficher le détails des erreurs',
    ],
];
