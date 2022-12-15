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
use UserFrosting\Sprinkle\ConfigManager\Controller\DisplayPage;
use UserFrosting\Sprinkle\ConfigManager\Controller\UpdateSchema;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/settings', function (RouteCollectorProxy $group) {
            $group->get('', DisplayPage::class)->setName('ConfigManager');
            $group->post('/{schema}', UpdateSchema::class)->setName('ConfigManager.save');
        });
    }
}
