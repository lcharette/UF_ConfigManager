<?php

    use Illuminate\Database\Schema\Blueprint;
    use UserFrosting\Sprinkle\Account\Model\Permission;
    use UserFrosting\Sprinkle\Account\Model\Role;

    /**
     * Permissions now replace the 'authorize_group' and 'authorize_user' tables.
     * Also, they now map many-to-many to roles.
     */
    if ($schema->hasTable('permissions')) {

        // Add default permissions
        $permissions = [
            'update_site_config' => new Permission([
                'slug' => 'update_site_config',
                'name' => 'Update site configuration',
                'conditions' => 'always()',
                'description' => 'Edit site configuration from the UI'
            ])
        ];

        foreach ($permissions as $slug => $permission) {
            $permission->save();
        }

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->attach([
                $permissions['update_site_config']->id
            ]);
        }

        echo "Created Config Manager permissions..." . PHP_EOL;
    } else {
        echo "Table 'permissions' doesn't exists yet.  Skipping..." . PHP_EOL;
    }