<?php
/**
 * UF Config Manager
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2016 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

$app->group('/admin/settings', function () {
    $this->get('', 'UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController:displayMain')
         ->setName('ConfigManager');

    $this->post('/{schema}', 'UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController:update')
         ->setName('ConfigManager.save');
});