<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Integration;

use Mockery;
use Illuminate\Cache\Repository as Cache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class ConfigManagerTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testConstructor(): void
    {
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $cache = Mockery::mock(Cache::class);
        $config = Mockery::mock(Config::class);

        $manager = new ConfigManager($locator, $cache, $config);
        $this->assertInstanceOf(ConfigManager::class, $manager);
    }

    /**
     * @depends testConstructor
     */
    public function testgetAllShemas(): void
    {
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResources')
            ->with('schema://config', true, false)
            ->andReturn([__DIR__ . '/schema/config'])
            ->getMock();
        $cache = Mockery::mock(Cache::class);
        $config = Mockery::mock(Config::class);

        $manager = new ConfigManager($locator, $cache, $config);
        $schema = $manager->getAllShemas();

        $this->assertIsArray($schema);
        $this->assertSame([
            'foo' => [
                'name'   => 'Foo Settings',
                'desc'   => 'Foo Settings for testing',
                'config' => [
                    'foo.foo' => [
                        'validators' => [
                        ],
                        'cached' => true,
                        'form'   => [
                            'type'  => 'text',
                            'label' => 'Foo Foo',
                            'icon'  => '',
                        ],
                    ],
                    'foo.bar' => [
                        'validators' => [
                        ],
                        'cached' => false,
                        'form'   => [
                            'type'  => 'text',
                            'label' => 'Foo Bar',
                            'icon'  => '',
                        ],
                    ],
                ],
                'filename' => 'foo',
            ],
            'test' => [
                'name'   => 'Test Settings',
                'desc'   => 'Test Settings for testing',
                'config' => [
                    'test.foo' => [
                        'validators' => [
                        ],
                        'cached' => true,
                        'form'   => [
                            'type'  => 'text',
                            'label' => 'Test Foo',
                            'icon'  => '',
                        ],
                    ],
                    'test.bar' => [
                        'validators' => [
                            'required' => [],
                        ],
                        'cached' => false,
                        'form'   => [
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

    /**
     * @depends testConstructor
     */
    public function testSetWithoutCache(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $cache = Mockery::mock(Cache::class)
            ->shouldNotReceive('forget')
            ->getMock();
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
        $this->assertSame('foo', $settings->first()->key);
        $this->assertSame('rab', $settings->first()->value);
        $this->assertSame(false, $settings->first()->cached);
    }

    /**
     * @depends testSetWithoutCache
     */
    public function testSetWithCache(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('forget')->with('UF_config')->once()
            ->getMock();
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
        $this->assertSame('foo', $settings->first()->key);
        $this->assertSame('bar', $settings->first()->value);
        $this->assertSame(true, $settings->first()->cached);
    }

    /**
     * @depends testSetWithCache
     */
    public function testRemove(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('forget')->with('UF_config')->twice()
            ->getMock();
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

    /**
     * @depends testSetWithCache
     */
    public function testRemoveActual(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        /** @var Config */
        $config = $this->ci->config;

        $manager = new ConfigManager($this->ci->locator, $this->ci->cache, $config);

        // Start with non existing setting in the db
        $this->assertFalse($manager->delete('foo'));

        // Start with no config
        $this->assertNull($config['foo']);

        // Set once
        $manager->set('foo', 'bar');

        // Make sure config is now set
        $this->assertSame('bar', $config['foo']);

        // Delete it
        $result = $manager->delete('foo');
        $this->assertTrue($result);

        // Make sure delete worked with no config
        $this->assertNull($config['foo']);
    }

    /**
     * @depends testSetWithCache
     */
    public function testfetchActual(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        /** @var Cache */
        $cache = $this->ci->cache;

        $manager = new ConfigManager($this->ci->locator, $cache, $this->ci->config);

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

        // Refetch, this time 'bar' will be refetched, but foo will be loaded from cache.
        // Result is same, this can only be seen by codecoverage & other unit tests.
        $this->assertEquals(['foo' => 'bar', 'bar' => '321'], $manager->fetch());
        $this->assertEquals(['foo' => 'bar'], $cache->get('UF_config'));

        // Force change the cached value
        $manager->set('foo', 'rab', true);

        // Cache will be flushed by set, and is now empty
        $this->assertNull($cache->get('UF_config'));

        // Refetch, this time everything will be refetched as cache is empty.
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

    public function testInvoke(): void
    {
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $cache = Mockery::mock(Cache::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $config = Mockery::mock(Config::class)
            ->shouldReceive('mergeItems')->with(null, ['foo' => 'bar'])->once()
            ->getMock();

        $manager = Mockery::mock(ConfigManager::class, [$locator, $cache, $config])
            ->makePartial()
            ->shouldReceive('fetch')->once()->andReturn(['foo' => 'bar'])
            ->getMock();

        $next = function ($c_request, $c_response) use ($request, $response) {
            $this->assertSame($request, $c_request);
            $this->assertSame($response, $c_response);

            return 'foo';
        };

        $result = $manager->__invoke($request, $response, $next);

        $this->assertSame('foo', $result);
    }

    public function testInvokeWithDB(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        // Flusch cache to prevent issue with previous tests
        $this->ci->cache->forget('UF_config');

        $manager = new ConfigManager($this->ci->locator, $this->ci->cache, $this->ci->config);

        $setting = new Setting(['key' => 'foo', 'value'  => 'bar', 'cached' => true]);
        $setting->save();

        $setting = new Setting(['key' => 'bar', 'value'  => '123', 'cached' => false]);
        $setting->save();

        $this->assertNull($this->ci->config->get('foo'));
        $this->assertNull($this->ci->config->get('bar'));

        $manager($request, $response, function ($request, $response) {
        });

        $this->assertSame('bar', $this->ci->config->get('foo'));
        $this->assertSame('123', $this->ci->config->get('bar'));
    }
}
