<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\App;

use UserFrosting\Sprinkle\ConfigManager\ConfigManager;
use UserFrosting\Sprinkle\SprinkleRecipe;

class App implements SprinkleRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'UserFrosting';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
            ConfigManager::class,
        ];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @return string[]
     */
    public function getRoutes(): array
    {
        return [
            Routes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions files.
     *
     * @return string[]
     */
    public function getServices(): array
    {
        return [];
    }
}
