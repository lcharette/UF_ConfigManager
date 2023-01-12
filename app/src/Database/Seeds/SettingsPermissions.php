<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Seeder for the required permissions for the Config Manager sprinkle.
 */
class SettingsPermissions implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        // Check if permission exists
        /** @var Permission|null */
        $permission = Permission::where('slug', 'update_site_config')->first();
        if ($permission === null) {
            // Add default permissions
            $permission = new Permission([
                'slug'        => 'update_site_config',
                'name'        => 'Update site configuration',
                'conditions'  => 'always()',
                'description' => 'Edit site configuration from the UI',
            ]);
            $permission->save();
        }

        // Add permission to role admin
        /** @var Role|null */
        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin !== null) {
            $roleSiteAdmin->permissions()->attach([
                $permission->id,
            ]);
        }
    }
}
