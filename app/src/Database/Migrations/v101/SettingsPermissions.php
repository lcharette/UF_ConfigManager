<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v101;

use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v100\SettingsTable;
use UserFrosting\Sprinkle\ConfigManager\Database\Seeds\SettingsPermissions as SettingsPermissionsSeed;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Settings permissions migration.
 */
class SettingsPermissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        PermissionsTable::class,
        SettingsTable::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        (new SettingsPermissionsSeed())->run();
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $permissions = Permission::where('slug', 'update_site_config')->get();
        foreach ($permissions as $permission) {
            $permission->delete();
        }
    }
}
