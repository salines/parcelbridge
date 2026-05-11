<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\UsersController Test Case
 *
 * @link \App\Controller\Api\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Clients',
    ];

    public function testLoginReturnsJsonUser(): void
    {
        $this->jsonRequest();

        $this->post('/api/login', [
            'email' => 'admin@example.test',
            'password' => 'password123',
        ]);

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $payload = $this->jsonPayload();
        $this->assertSame('admin@example.test', $payload['user']['email']);
        $this->assertSame('admin', $payload['user']['role']['value']);
    }

    public function testLoginRejectsInvalidPassword(): void
    {
        $this->jsonRequest();

        $this->post('/api/login', [
            'email' => 'admin@example.test',
            'password' => 'wrong',
        ]);

        $this->assertResponseCode(401);
        $this->assertContentType('application/json');
        $this->assertSame('Invalid email or password.', $this->jsonPayload()['error']['message']);
    }

    public function testProtectedEndpointRequiresAuthentication(): void
    {
        $this->jsonRequest();

        $this->get('/api/me');

        $this->assertResponseCode(401);
    }

    private function jsonRequest(): void
    {
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function jsonPayload(): array
    {
        return json_decode((string)$this->_response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }
}
