<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\ShipRequestsController Test Case
 *
 * @link \App\Controller\Api\ShipRequestsController
 */
class ShipRequestsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PackageStatusHistories',
        'app.PackagesShipRequests',
        'app.ShipRequests',
        'app.Packages',
        'app.Clients',
        'app.Users',
    ];

    public function testAddCreatesShipRequest(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $shipRequests = TableRegistry::getTableLocator()->get('ShipRequests');
        $beforeCount = $shipRequests->find()->count();
        $packages->updateAll(['status' => 'invoice_approved'], ['id' => 1]);
        $this->loginClient();
        $this->jsonRequest();

        $this->post('/api/ship-requests', [
            'package_ids' => [1],
        ]);

        $this->assertResponseCode(201);
        $this->assertContentType('application/json');
        $payload = $this->jsonPayload();
        $this->assertSame('submitted', $payload['ship_request']['status']['value']);
        $this->assertSame($beforeCount + 1, $shipRequests->find()->count());
        $this->assertSame('ship_requested', $packages->get(1)->status->value);
    }

    public function testAddRejectsPackageThatIsNotApproved(): void
    {
        $this->loginClient();
        $this->jsonRequest();

        $this->post('/api/ship-requests', [
            'package_ids' => [1],
        ]);

        $this->assertResponseCode(400);
        $this->assertContentType('application/json');
        $this->assertSame('Only approved packages can be submitted.', $this->jsonPayload()['error']['message']);
        $this->assertSame('ready_to_send', TableRegistry::getTableLocator()->get('Packages')->get(1)->status->value);
    }

    private function loginClient(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
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
