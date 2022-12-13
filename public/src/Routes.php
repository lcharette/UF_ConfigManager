<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\App;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->redirect('/', '/dashboard')->setName('index');
    }
}
