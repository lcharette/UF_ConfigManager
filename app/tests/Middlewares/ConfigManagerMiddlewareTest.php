<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Middlewares;

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Sprinkle\ConfigManager\Middlewares\ConfigManagerMiddleware;
use UserFrosting\Sprinkle\ConfigManager\Tests\TestCase;
use UserFrosting\Sprinkle\ConfigManager\Util\ConfigManager;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\UniformResourceLocator\ResourceLocator;

class ConfigManagerMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    public function testProcess(): void
    {
        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('mergeItems')->with(null, ['foo' => 'bar'])->once()
            ->getMock();

        /** @var ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var ResponseInterface */
        $response = Mockery::mock(ResponseInterface::class);

        /** @var RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')->with($request)->once()->andReturn($response)
            ->getMock();

        /** @var ConfigManager */
        $manager = Mockery::mock(ConfigManager::class)
            ->shouldReceive('fetch')->once()->andReturn(['foo' => 'bar'])
            ->getMock();

        $middleware = new ConfigManagerMiddleware($manager, $config);
        $result = $middleware->process($request, $handler);
    }

    public function testInvokeWithDB(): void
    {
        $this->refreshDatabase();

        $request = Mockery::mock(ServerRequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        /** @var ResourceLocator */
        $locator = $this->ci->get(ResourceLocator::class);

        /** @var Cache */
        $cache = $this->ci->get(Cache::class);

        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')->with($request)->once()->andReturn($response)
            ->getMock();

        // Flush cache to prevent issue with previous tests
        $cache->forget('UF_config');

        $manager = new ConfigManager($locator, $cache, $config);
        $middleware = new ConfigManagerMiddleware($manager, $config);

        $setting = new Setting(['key' => 'foo', 'value'  => 'bar', 'cached' => true]);
        $setting->save();

        $setting = new Setting(['key' => 'bar', 'value'  => '123', 'cached' => false]);
        $setting->save();

        $this->assertNull($config->get('foo'));
        $this->assertNull($config->get('bar'));

        $result = $middleware->process($request, $handler);

        $this->assertSame('bar', $config->get('foo'));
        $this->assertSame('123', $config->get('bar'));
    }
}
