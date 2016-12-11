<?php
/**
 * UF Config Manager
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2016 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */
namespace UserFrosting\Sprinkle\ConfigManager;

use UserFrosting\Sprinkle\ConfigManager\ServicesProvider\ConfigManagerServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

/**
 * ConfigManager class.
 *
 * Bootstrapper class for the 'Settings' sprinkle.
 * @extends Sprinkle
 */
class ConfigManager extends Sprinkle
{
    /**
     * Register services.
     */
    public function init()
    {
        $serviceProvider = new ConfigManagerServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
