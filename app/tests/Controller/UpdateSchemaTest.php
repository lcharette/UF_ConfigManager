<?php

declare(strict_types=1);

/*
 * UF Config Manager Sprinkle
 *
 * @link      https://github.com/lcharette/UF_ConfigManager
 * @copyright Copyright (c) 2020 Louis Charette
 * @license   https://github.com/lcharette/UF_ConfigManager/blob/master/LICENSE (MIT License)
 */

namespace UserFrosting\Sprinkle\ConfigManager\Tests\Controller;

use UserFrosting\Alert\AlertStream;
use UserFrosting\App\App;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\ConfigManager\Database\Models\Setting;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Testing\TestCase;
use UserFrosting\UniformResourceLocator\ResourceLocation;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class UpdateSchemaTest extends TestCase
{
    use RefreshDatabase;
    use WithTestUser;

    // Use public testing App for this test
    protected string $mainSprinkle = App::class;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testDisplayMainWithNoAuth(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/settings/site');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(403, $response);
    }

    public function testUpdateWithNoData(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_site_config']);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/settings/foo');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Missing Data',
            'description' => 'POST data is missing or invalid',
            'status'      => 400,
        ], $response);
    }

    public function testUpdateWithNoSchema(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_site_config']);

        // Create request with method and url and fetch response
        $data = ['data' => [
            'test.foo' => '123bar',
            'test.bar' => 'foo123',
        ]];
        $request = $this->createJsonRequest('POST', '/settings/foo', $data);
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(404, $response);
        $this->assertJsonResponse([
            'title'       => 'Schema not found',
            'description' => 'Schema foo not found',
            'status'      => 404,
        ], $response);
    }

    public function testUpdate(): void
    {
        // Add test schema
        /** @var ResourceLocatorInterface */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->addLocation(new ResourceLocation('test', __DIR__ . '/../'));

        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_site_config']);

        // Assert the initial db state
        $result = Setting::where('key', 'test.foo')->first();
        $this->assertNull($result);

        // Create request with method and url and fetch response
        $data = ['data' => [
            'test.foo' => '123bar',
            'test.bar' => 'foo123',
        ]];
        $request = $this->createJsonRequest('POST', '/settings/test', $data);
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertJsonResponse([], $response);
        $this->assertResponseStatus(200, $response);

        // Assert new db state
        $result = Setting::where('key', 'test.foo')->first();
        $this->assertSame('123bar', $result->value); // @phpstan-ignore-line

        // Test alert
        /** @var AlertStream */
        $ms = $this->ci->get(AlertStream::class);
        $messages = $ms->getAndClearMessages();
        $this->assertSame('success', array_reverse($messages)[0]['type']);
    }

    public function testUpdateWithValidationErrors(): void
    {
        // Add test schema
        /** @var ResourceLocatorInterface */
        $locator = $this->ci->get(ResourceLocatorInterface::class);
        $locator->addLocation(new ResourceLocation('test', __DIR__ . '/../'));

        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_site_config']);

        // Create request with method and url and fetch response
        $data = ['data' => [
            'test.foo' => '123bar',
        ]];
        $request = $this->createJsonRequest('POST', '/settings/test', $data);
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => "'test.bar' is required",
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }
}
