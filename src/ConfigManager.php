<?php
/**
 * UF Config Manager
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2016 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */
namespace UserFrosting\Sprinkle\ConfigManager;

use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager as CM;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Gaston\Exception\GastonException;
use UserFrosting\Sprinkle\Gaston\Exception\GastonFatalException;

/**
 * Settings class.
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
        // Using the `SettingsManager`, we merge all the available settings from the db into the main `config`
        // Everything loaded before this point CAN'T be overwritten by the db settings and every Sprinkle loaded
        // AFTER this one will overwrite the settings from the db
        $this->ci->db;
        $ConfigManager = new CM($this->ci);
        $this->ci->config->mergeItems(null, $ConfigManager->fetch());
    }
}
