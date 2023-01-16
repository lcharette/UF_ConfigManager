<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager;

use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v100\SettingsTable;
use UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v101\SettingsPermissions;
use UserFrosting\Sprinkle\ConfigManager\Database\Seeds\SettingsPermissions as SettingsPermissionsSeed;
use UserFrosting\Sprinkle\ConfigManager\Middlewares\ConfigManagerMiddleware;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\FormGenerator\FormGenerator;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class ConfigManager implements SprinkleRecipe, MigrationRecipe, SeedRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Config Manager';
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
     *
     * @codeCoverageIgnore
     */
    public function getBakeryCommands(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
            Admin::class,
            AdminLTE::class,
            FormGenerator::class,
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

    /**
     * Returns a list of all Middlewares classes.
     *
     * {@inheritdoc}
     */
    public function getMiddlewares(): array
    {
        return [
            ConfigManagerMiddleware::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMigrations(): array
    {
        return [
            SettingsTable::class,
            SettingsPermissions::class,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getSeeds(): array
    {
        return [
            SettingsPermissionsSeed::class,
        ];
    }
}
