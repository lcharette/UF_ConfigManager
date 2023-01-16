<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Util;

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Sprinkle\ConfigManager\Tests\TestCase;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class ConfigManagerTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    public function testGetAllSchemas(): void
    {
        /** @var ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResources')
            ->with('schema://config', true, false)
            ->andReturn([__DIR__ . '/../data/schema/config'])
            ->getMock();
        $cache = Mockery::mock(Cache::class);
        $config = Mockery::mock(Config::class);

        $manager = new ConfigManager($locator, $cache, $config);
        $schema = $manager->getAllSchemas();

        $this->assertSame([
            'foo'  => [
                'name'     => 'Foo Settings',
                'desc'     => 'Foo Settings for testing',
                'config'   => [
                    'foo.foo' => [
                        'validators' => [
                        ],
                        'cached'     => true,
                        'form'       => [
                            'type'  => 'text',
                            'label' => 'Foo Foo',
                            'icon'  => '',
                        ],
                    ],
                    'foo.bar' => [
                        'validators' => [
                        ],
                        'cached'     => false,
                        'form'       => [
                            'type'  => 'text',
                            'label' => 'Foo Bar',
                            'icon'  => '',
                        ],
                    ],
                ],
                'filename' => 'foo',
            ],
            'test' => [
                'name'     => 'Test Settings',
                'desc'     => 'Test Settings for testing',
                'config'   => [
                    'test.foo' => [
                        'validators' => [
                        ],
                        'form'       => [
                            'type'  => 'text',
                            'label' => 'Test Foo',
                            'icon'  => '',
                        ],
                    ],
                    'test.bar' => [
                        'validators' => [
                            'required' => [],
                        ],
                        'cached'     => false,
                        'form'       => [
                            'type'  => 'text',
                            'label' => 'Test Bar',
                            'icon'  => '',
                        ],
                    ],
                ],
                'filename' => 'test',
            ],
        ], $schema);
    }

    public function testSetWithoutCache(): void
    {
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);

        /** @var Cache */
        $cache = Mockery::mock(Cache::class)
            ->shouldNotReceive('forget')
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('set')->with('foo', 'bar')->once()
            ->shouldReceive('set')->with('foo', 'rab')->once()
            ->getMock();

        $manager = new ConfigManager($locator, $cache, $config);

        // Set once
        $result = $manager->set('foo', 'bar', false);
        $this->assertTrue($result);

        // Set again. It exist, and same value, so should return false
        $result = $manager->set('foo', 'bar', false);
        $this->assertFalse($result);

        // Set again, It exist, but not the same value
        $result = $manager->set('foo', 'rab', false);
        $this->assertTrue($result);

        // Get the actual table to make sure it worked
        $settings = Setting::all();
        $this->assertCount(1, $settings);

        /** @var Setting */
        $first = $settings->first();
        $this->assertSame('foo', $first->key);
        $this->assertSame('rab', $first->value);
        $this->assertSame(false, $first->cached);
    }

    public function testSetWithCache(): void
    {
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);

        /** @var Cache */
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('forget')->with('UF_config')->once()
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('set')->with('foo', 'bar')->once()
            ->getMock();

        $manager = new ConfigManager($locator, $cache, $config);

        // Set once
        $result = $manager->set('foo', 'bar', true);
        $this->assertTrue($result);

        // Get the actual table to make sure it worked
        $settings = Setting::all();
        $this->assertCount(1, $settings);

        /** @var Setting */
        $first = $settings->first();
        $this->assertSame('foo', $first->key);
        $this->assertSame('bar', $first->value);
        $this->assertSame(true, $first->cached);
    }

    public function testRemove(): void
    {
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);

        /** @var Cache */
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('forget')->with('UF_config')->twice()
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('set')->with('foo', 'bar')->once()
            ->shouldReceive('offsetUnset')->with('foo')->once()
            ->getMock();

        $manager = new ConfigManager($locator, $cache, $config);

        // Start with non existing setting in the db
        $result = $manager->delete('foo');
        $this->assertFalse($result);

        // Set once then delete it
        $manager->set('foo', 'bar', true);
        $result = $manager->delete('foo');
        $this->assertTrue($result);
    }

    public function testRemoveActual(): void
    {
        $this->refreshDatabase();

        /** @var ResourceLocator */
        $locator = $this->ci->get(ResourceLocator::class);

        /** @var Cache */
        $cache = $this->ci->get(Cache::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        $manager = new ConfigManager($locator, $cache, $config);

        // Start with non existing setting in the db
        $this->assertFalse($manager->delete('foo'));

        // Start with no config
        $this->assertNull($config->get('foo'));

        // Set once
        $manager->set('foo', 'bar');

        // Make sure config is now set
        $this->assertSame('bar', $config->get('foo'));

        // Delete it
        $result = $manager->delete('foo');
        $this->assertTrue($result); // @phpstan-ignore-line False positive

        // Make sure delete worked with no config
        $this->assertNull($config->get('foo'));
    }

    public function testFetchActual(): void
    {
        $this->refreshDatabase();

        /** @var ResourceLocator */
        $locator = $this->ci->get(ResourceLocator::class);

        /** @var Cache */
        $cache = $this->ci->get(Cache::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        $manager = new ConfigManager($locator, $cache, $config);

        // No result by default
        $this->assertNull($cache->get('UF_config'));
        $this->assertSame([], $manager->fetch());
        $this->assertSame([], $cache->get('UF_config'));

        // Add test config, one cached one non-cached
        $manager->set('foo', 'bar', true);
        $manager->set('bar', '123', false);
        $this->assertNull($cache->get('UF_config')); // Back to null, because of addition.

        // Fetch newly created
        $this->assertEquals(['foo' => 'bar', 'bar' => '123'], $manager->fetch());
        $this->assertEquals(['foo' => 'bar'], $cache->get('UF_config')); // Bar is not cached !

        // Force change the non cached value
        $manager->set('bar', '321', false);

        // Cache won't be flushed
        $this->assertEquals(['foo' => 'bar'], $cache->get('UF_config'));

        // Refetch, this time 'bar' will be re-fetched, but foo will be loaded from cache.
        // Result is same, this can only be seen by code coverage & other unit tests.
        $this->assertEquals(['foo' => 'bar', 'bar' => '321'], $manager->fetch());
        $this->assertEquals(['foo' => 'bar'], $cache->get('UF_config'));

        // Force change the cached value
        $manager->set('foo', 'rab', true);

        // Cache will be flushed by set, and is now empty
        $this->assertNull($cache->get('UF_config'));

        // Refetch, this time everything will be re-fetched as cache is empty.
        $this->assertEquals(['foo' => 'rab', 'bar' => '321'], $manager->fetch());
        $this->assertEquals(['foo' => 'rab'], $cache->get('UF_config')); // Bar is still not cached !

        // Finally, we'll set bar as cached now !
        // Cache will be flushed by set, and is now empty
        // Fetch will load everything from DB, and both will be cached
        $manager->set('bar', 'bar', true);
        $this->assertNull($cache->get('UF_config'));
        $this->assertEquals(['bar' => 'bar', 'foo' => 'rab'], $manager->fetch());
        $this->assertEquals(['foo' => 'rab', 'bar' => 'bar'], $cache->get('UF_config')); // Bar is still not cached !
    }
}
