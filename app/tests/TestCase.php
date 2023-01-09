<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests;

use UserFrosting\Sprinkle\ConfigManager\ConfigManager;
use UserFrosting\Testing\TestCase as UFTestCase;

/**
 * Test case with ConfigManager as main sprinkle
 */
class TestCase extends UFTestCase
{
    protected string $mainSprinkle = ConfigManager::class;
}
