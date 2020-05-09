<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Unit;

use Mockery;
use Illuminate\Cache\Repository as Cache;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class ConfigManagerTest extends TestCase
{
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

        $helper = new ConfigManager($locator, $cache, $config);
        $this->assertInstanceOf(ConfigManager::class, $helper);
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

        $helper = new ConfigManager($locator, $cache, $config);

        $schema = $helper->getAllShemas();

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
                        ],
                        'cached' => false,
                        'form'   => [
                            'type'  => 'text',
                            'label' => 'Test Bar',
                            'icon'  => '',
                        ],
                    ],
                ],
                'filename' => 'test'
            ],
        ], $schema);
    }
}
