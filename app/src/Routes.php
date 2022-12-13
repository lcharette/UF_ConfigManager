<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->redirect('/', '/dashboard')->setName('index');
        $app->group('/settings', function (RouteCollectorProxy $group) {
            $group->get('', [ConfigManagerController::class, 'displayMain'])->setName('ConfigManager');
            $group->post('/{schema}', [ConfigManagerController::class, 'update'])->setName('ConfigManager.save');
        });
    }
}
