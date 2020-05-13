<?php

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Integration;

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Sprinkle\Core\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Router;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class ConfigManagerControllerTest extends TestCase
{
    use withController;
    use TestDatabase;
    use RefreshDatabase;

    public function testConstructor()
    {
        $controller = new ConfigManagerController($this->ci);
        $this->assertInstanceOf(ConfigManagerController::class, $controller);
    }

    /**
     * @depends testdisplayMain
     */
    public function testdisplayMainWithNoAuth(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $this->ci['currentUser'] = $user;

        $authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($user, 'update_site_config')->andReturn(false)
            ->getMock();
        $this->ci['authorizer'] = $authorizer;

        $controller = new ConfigManagerController($this->ci);

        $this->expectException(ForbiddenException::class);
        $controller->displayMain($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testConstructor
     */
    public function testdisplayMain(): void
    {
        // Get requests & response
        $response = $this->getResponse();
        $request = $this->getRequest();

        $expectation = [
            'foo' => [
                'name'     => 'Foo Settings',
                'desc'     => 'Foo Settings for testing',
                'filename' => 'foo',
                'fields'   => [
                    'data[foo.foo]' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => '',
                        'name'         => 'data[foo.foo]',
                        'id'           => 'field_data[foo.foo]',
                        'type'         => 'text',
                        'label'        => 'Foo Foo',
                        'icon'         => '',
                    ],
                    'data[foo.bar]' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => '',
                        'name'         => 'data[foo.bar]',
                        'id'           => 'field_data[foo.bar]',
                        'type'         => 'text',
                        'label'        => 'Foo Bar',
                        'icon'         => '',
                    ],
                ],
                'validators' => '{
    "rules": {
        "foo.foo": [],
        "foo.bar": []
    },
    "messages": []
}',
                'formAction' => '/settings/foo',
            ],
            'test' => [
                'name'     => 'Test Settings',
                'desc'     => 'Test Settings for testing',
                'filename' => 'test',
                'fields'   => [
                    'data[test.foo]' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => '',
                        'name'         => 'data[test.foo]',
                        'id'           => 'field_data[test.foo]',
                        'type'         => 'text',
                        'label'        => 'Test Foo',
                        'icon'         => '',
                    ],
                    'data[test.bar]' => [
                        'autocomplete' => 'off',
                        'class'        => 'form-control',
                        'value'        => '',
                        'name'         => 'data[test.bar]',
                        'id'           => 'field_data[test.bar]',
                        'type'         => 'text',
                        'label'        => 'Test Bar',
                        'icon'         => '',
                    ],
                ],
                'validators' => '{
    "rules": {
        "test.foo": [],
        "test.bar": {
            "required": true
        }
    },
    "messages": []
}',
                'formAction' => '/settings/test',
            ],
        ];

        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResources')
            ->with('schema://config', true, false)
            ->andReturn([__DIR__ . '/schema/config'])
            ->getMock();
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class)
            ->shouldReceive('all')->andReturn([])
            ->getMock();
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->translator = Mockery::mock($this->ci->translator); // Referencing the ci here makes Translator/MessageTranslator automatically depending of the UF version
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();
        $ci->router = Mockery::mock(Router::class)
            ->shouldReceive('pathFor')->with('ConfigManager.save', ['schema' => 'foo'])->andReturn('/settings/foo')
            ->shouldReceive('pathFor')->with('ConfigManager.save', ['schema' => 'test'])->andReturn('/settings/test')
            ->getMock();
        $ci->view = Mockery::mock(Twig::class)
            ->shouldReceive('render')->with(
                $response,
                'pages/ConfigManager.html.twig',
                [
                    'schemas' => $expectation,
                ]
            )->once()->andReturn($response)->getMock();

        // ----

        // Get controller
        $controller = new ConfigManagerController($ci);

        // Get and analyse response
        $result = $controller->displayMain($request, $response, []);
        $this->assertInstanceOf(ResponseInterface::class, $result);
    }

    /**
     * @depends testUpdate
     */
    public function testupdateWithNoAuth(): void
    {
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class);
        $ci->alerts = Mockery::mock(AlertStream::class);
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class);
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->andReturn(false)
            ->getMock();

        $controller = new ConfigManagerController($ci);

        $this->expectException(ForbiddenException::class);
        $controller->update($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateWithNoSchema(): void
    {
        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class);
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class);
        $ci->alerts = Mockery::mock(AlertStream::class);
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();

        // Get controller
        $controller = new ConfigManagerController($ci);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Schema not defined.');
        $controller->update($this->getRequest(), $this->getResponse(), []);
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateWithNoData(): void
    {
        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class);
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class);
        $ci->alerts = Mockery::mock(AlertStream::class);
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();

        // Get controller
        $controller = new ConfigManagerController($ci);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Data not found.');
        $controller->update($this->getRequest(), $this->getResponse(), ['schema' => 'test']);
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateWithSchemaDontExist(): void
    {
        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')
            ->with('schema://config/bar.json')
            ->andReturn(false)
            ->getMock();
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class);
        $ci->alerts = Mockery::mock(AlertStream::class);
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();

        // Get controller
        $controller = new ConfigManagerController($ci);

        // Get and analyse response
        $request = $this->getRequest()->withParsedBody([
            'data' => [
                'test.foo' => '123bar',
            ],
        ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Schema bar not found.');
        $controller->update($request, $this->getResponse(), ['schema' => 'bar']);
    }

    /**
     * @depends testConstructor
     */
    public function testUpdate(): void
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')
            ->with('schema://config/test.json')
            ->andReturn(__DIR__ . '/schema/config/test.json')
            ->once()
            ->getMock();
        $ci->alerts = Mockery::mock(AlertStream::class)
            ->shouldReceive('addMessageTranslated')->with('success', 'SITE.CONFIG.SAVED')->once()
            ->getMock();
        $ci->cache = Mockery::mock(Cache::class)
            ->shouldReceive('forget')->with('UF_config')->once()
            ->getMock();
        $ci->config = Mockery::mock(Config::class)
            ->shouldReceive('set')->with('test.foo', '123bar')->once()
            ->shouldReceive('set')->with('test.bar', 'foo123')->once()
            ->getMock();
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->translator = Mockery::mock($this->ci->translator); // Referencing the ci here makes Translator/MessageTranslator automatically depending of the UF version
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();

        // Get controller
        $controller = new ConfigManagerController($ci);

        // Prepare POST data
        $request = $this->getRequest()->withParsedBody([
            'data' => [
                'test.foo' => '123bar',
                'test.bar' => 'foo123',
            ],
        ]);

        // Check the manager
        $result = Setting::where('key', 'test.foo')->first();
        $this->assertNull($result);

        $result = $controller->update($request, $this->getResponse(), ['schema' => 'test']);

        // Check assertions
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 200);
        $this->assertJson((string) $result->getBody());
        $this->assertSame('[]', (string) $result->getBody());

        // Check the manager
        $result = Setting::where('key', 'test.foo')->first();
        $this->assertSame('123bar', $result->value);
    }

    /**
     * @depends testUpdate
     */
    public function testUpdateWithValidationErrors(): void
    {
        // Create mock services
        $ci = Mockery::mock(ContainerInterface::class);
        $ci->locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')
            ->with('schema://config/test.json')
            ->andReturn(__DIR__ . '/schema/config/test.json')
            ->once()
            ->getMock();
        $ci->alerts = Mockery::mock(AlertStream::class)
            ->shouldReceive('addValidationErrors')->once()
            ->getMock();
        $ci->cache = Mockery::mock(Cache::class);
        $ci->config = Mockery::mock(Config::class);
        $ci->currentUser = Mockery::mock(UserInterface::class);
        $ci->translator = Mockery::mock($this->ci->translator); // Referencing the ci here makes Translator/MessageTranslator automatically depending of the UF version
        $ci->authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($ci->currentUser, 'update_site_config')->once()->andReturn(true)
            ->getMock();

        // Get controller
        $controller = new ConfigManagerController($ci);

        // Prepare POST data
        $request = $this->getRequest()->withParsedBody([
            'data' => [
                'test.foo' => '123bar',
            ],
        ]);

        $result = $controller->update($request, $this->getResponse(), ['schema' => 'test']);

        // Check assertions
        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame($result->getStatusCode(), 400);
    }
}
