<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\PackagesController Test Case
 *
 * @link \App\Controller\Api\PackagesController
 */
class PackagesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Packages',
        'app.Clients',
        'app.Users',
        'app.Invoices',
        'app.PackageStatusHistories',
        'app.ShipRequests',
        'app.PackagesShipRequests',
    ];

    public function testIndexReturnsScopedJsonPackages(): void
    {
        $this->loginClient();
        $this->jsonRequest();

        $this->get('/api/packages?q=Lorem');

        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $payload = $this->jsonPayload();
        $this->assertCount(1, $payload['packages']);
        $this->assertSame('Lorem ipsum dolor sit amet', $payload['packages'][0]['tracking_number']);
        $this->assertSame('ready_to_send', $payload['packages'][0]['status']['value']);
    }

    public function testViewRejectsAnotherClientPackage(): void
    {
        $packageId = $this->createOtherClientPackage();
        $this->loginClient();
        $this->jsonRequest();

        $this->get('/api/packages/' . $packageId);

        $this->assertResponseCode(403);
    }

    public function testReadyForPickupReturnsBusinessErrorAsJson(): void
    {
        $this->loginAdmin();
        $this->jsonRequest();
        $this->enableCsrfToken();

        $this->post('/api/packages/1/ready-for-pickup');

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');
        $this->assertStringContainsString(
            'Package status cannot transition',
            $this->jsonPayload()['error']['message'],
        );
    }

    public function testStateChangingEndpointRequiresCsrfToken(): void
    {
        $this->loginAdmin();
        $this->jsonRequest();

        $this->post('/api/packages/1/ready-for-pickup');

        $this->assertResponseCode(403);
    }

    private function loginAdmin(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Admin;
        $this->session(['Auth' => $user]);
    }

    private function loginClient(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
    }

    private function createOtherClientPackage(): int
    {
        $clients = TableRegistry::getTableLocator()->get('Clients');
        $client = $clients->newEntity([
            'user_id' => 2,
            'suite_number' => 'OTHER',
            'phone' => '555',
            'address_line_1' => 'Other street',
            'city' => 'Other city',
            'country' => 'AW',
        ]);
        $clients->saveOrFail($client);

        $packages = TableRegistry::getTableLocator()->get('Packages');
        $package = $packages->newEntity([
            'client_id' => $client->id,
            'tracking_number' => 'OTHER-TRACKING',
            'width' => 1,
            'height' => 1,
            'length' => 1,
            'weight' => 1,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Other client package',
            'status' => 'ready_to_send',
            'received_at' => '2026-05-09 09:00:00',
            'created_by_user_id' => 1,
        ]);
        $packages->saveOrFail($package);

        return (int)$package->id;
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
