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

use UserFrosting\App\App;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Testing\TestCase;

class DisplayPageTest extends TestCase
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
        $request = $this->createRequest('GET', '/settings');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(403, $response);
    }

    public function testDisplayMain(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_site_config']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/settings');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('', (string) $response->getBody());
    }
}
