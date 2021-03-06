<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * ConfigManager class.
 *
 * Bootstrapper class for the 'Settings' sprinkle.
 */
class ConfigManager extends Sprinkle
{
    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAddGlobalMiddleware' => ['onAddGlobalMiddleware', 0],
        ];
    }

    /**
     * Add middleware.
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        $app = $event->getApp();
        $app->add($this->ci->configManager);
    }
}
