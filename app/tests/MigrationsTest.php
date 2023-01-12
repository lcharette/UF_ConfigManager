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

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\ConfigManager\Database\Migrations\v101\SettingsPermissions;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Seeder\SeedRepositoryInterface;
use UserFrosting\Sprinkle\Core\Seeder\SprinkleSeedsRepository;

class MigrationsTest extends TestCase
{
    public function testMigration(): void
    {
        /** @var Migrator */
        $migrator = $this->ci->get(Migrator::class);

        /** @var Builder */
        $schema = $this->ci->get(Builder::class);

        // Assert initial db state
        $migrator->reset();
        $this->assertEquals([], $schema->getColumnListing('settings'));

        // Run Migration
        $migrator->migrate();

        // Define expectation
        $expectation = [
            'id',
            'key',
            'value',
            'cached',
            'created_at',
            'updated_at',
        ];

        // Get result
        $result = $schema->getColumnListing('settings');

        // Sort both, to avoid DB specific ordering
        sort($expectation);
        sort($result);

        // Assert new db state
        $this->assertEquals($expectation, $result);

        // Assert permission has been added
        $this->assertInstanceOf(Permission::class, Permission::where('slug', 'update_site_config')->first());

        // Assert Role doesn't have permission
        // @phpstan-ignore-next-line - first() return Role|null
        $this->assertNull(Role::where('slug', 'site-admin')->first()?->permissions()->where('slug', 'update_site_config')->first());

        // Run both seed and test again for role permissions
        /** @var SprinkleSeedsRepository */
        $seeds = $this->ci->get(SeedRepositoryInterface::class);
        foreach ($seeds as $seed) {
            $seed->run();
        }
        // @phpstan-ignore-next-line - first() return Role|null
        $this->assertInstanceOf(Permission::class, Role::where('slug', 'site-admin')->first()?->permissions()->where('slug', 'update_site_config')->first());

        // Rollback permission
        /** @var SettingsPermissions */
        $migration = $this->ci->get(SettingsPermissions::class);
        $migration->down();

        // Assert permission has been removed
        $this->assertNull(Permission::where('slug', 'update_site_config')->first());

        // Rollback and assert new db state
        $migrator->rollback();
        $this->assertEquals([], $schema->getColumnListing('settings'));
    }
}
