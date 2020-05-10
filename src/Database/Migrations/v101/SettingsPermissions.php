<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v101;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Settings permissions migration.
 */
class SettingsPermissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable',
        '\UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v100\SettingsTable',
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Check if permission exist
        $permissionExist = Permission::where('slug', 'update_site_config')->first();

        // @codeCoverageIgnoreStart
        if ($permissionExist) {
            $this->io->warning("\nPermission slug `update_site_config` already exist. Skipping...");

            return;
        }
        // @codeCoverageIgnoreEnd

        // Add default permissions
        $permission = new Permission([
            'slug'        => 'update_site_config',
            'name'        => 'Update site configuration',
            'conditions'  => 'always()',
            'description' => 'Edit site configuration from the UI',
        ]);
        $permission->save();

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->attach([
                $permission->id,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $permissions = Permission::where('slug', 'update_site_config')->get();
        foreach ($permissions as $permission) {
            $permission->delete();
        }
    }
}
