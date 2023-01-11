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
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;

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

        // Rollback and assert new db state
        $migrator->rollback();
        $this->assertEquals([], $schema->getColumnListing('settings'));

        // TODO : SettingsPermissions: Test permission exist (current code should not work)
        // TODO : SettingsPermissions: Test Role. With current code, permission will never be added on fresh one step install. SettingsPermissions should be moved to a seed.
    }
}
