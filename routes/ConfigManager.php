<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

$app->group('/settings', function () {
    $this->get('', 'UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController:displayMain')
         ->setName('ConfigManager');

    $this->post('/{schema}', 'UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController:update')
         ->setName('ConfigManager.save');
});
