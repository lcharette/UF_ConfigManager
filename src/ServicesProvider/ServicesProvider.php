<?php
/**
 * Gaston (http://gaston.bbqsoftwares.com)
 *
 * @link      https://github.com/lcharette/GASTON
 * @copyright Copyright (c) 2016 Louis Charette
 * @license
 */
namespace UserFrosting\Sprinkle\ConfigManager\ServicesProvider;

use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;

/**
 * ConfigManagerServicesProvider class.
 * Registers services for the ConfigManager sprinkle, such as configManager, etc.
 */
class ServicesProvider
{
    /**
     * Register configManager services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        $container['configManager'] = function ($c) {
            $c->db;
            return new ConfigManager($c);
        };
    }
}
