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
use Slim\Views\Twig;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\ConfigManager\Controller\ConfigManagerController;
use UserFrosting\Sprinkle\Core\Router;
use UserFrosting\Sprinkle\Core\Tests\withController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Tests\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class ConfigManagerControllerTest extends TestCase
{
    use withController;

    public function testConstructor()
    {
        $controller = new ConfigManagerController($this->ci);
        $this->assertInstanceOf(ConfigManagerController::class, $controller);

        return $controller;
    }

    /**
     * @depends testConstructor
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
        "test.bar": []
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
        $ci->translator = Mockery::mock(Translator::class);
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
            )->once()->getMock();

        // ----

        // Get controller
        $controller = new ConfigManagerController($ci);

        // Get and analyse response
        $result = $controller->displayMain($request, $response, []);
        //$this->assertEquals($expectation, $result);
        $this->assertNull($result);
    }

    /**
     * @depends testConstructor
     */
    public function testupdateWithNoAuth(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $this->ci['currentUser'] = $user;

        $authorizer = Mockery::mock(AuthorizationManager::class)
            ->shouldReceive('checkAccess')->with($user, 'update_site_config')->andReturn(false)
            ->getMock();
        $this->ci['authorizer'] = $authorizer;

        $controller = new ConfigManagerController($this->ci);

        $this->expectException(ForbiddenException::class);
        $controller->update($this->getRequest(), $this->getResponse(), []);
    }
}
