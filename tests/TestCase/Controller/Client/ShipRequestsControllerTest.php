<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Client\ShipRequestsController Test Case
 *
 * @link \App\Controller\Client\ShipRequestsController
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

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Client\ShipRequestsController::index()
     */
    public function testIndex(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    public function testExportCsv(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests/export-csv?q=Lorem');

        $this->assertResponseOk();
        $this->assertContentType('text/csv');
        $this->assertHeaderContains('Content-Disposition', 'my-ship-requests.csv');
        $this->assertResponseContains('Processing Reference');
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testManifestPdf(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests/manifest/1');

        $this->assertResponseOk();
        $this->assertContentType('application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'ship-request-1-manifest.pdf');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Client\ShipRequestsController::view()
     */
    public function testView(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Ship Request');
        $this->assertResponseContains('Related Packages');
        $this->assertResponseContains('href="/client/packages/view/1"');
    }

    public function testViewRejectsAnotherClientShipRequest(): void
    {
        $shipRequestId = $this->createOtherClientShipRequest();
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests/view/' . $shipRequestId);

        $this->assertResponseCode(403);
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Client\ShipRequestsController::add()
     */
    public function testAdd(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $shipRequests = TableRegistry::getTableLocator()->get('ShipRequests');
        $beforeCount = $shipRequests->find()->count();
        $packages->updateAll(['status' => 'invoice_approved'], ['id' => 1]);

        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/ship-requests/add', [
            'packages' => [
                '_ids' => [1],
            ],
        ]);

        $this->assertRedirect('/client/ship-requests');
        $this->assertSame($beforeCount + 1, $shipRequests->find()->count());
        $this->assertSame('ship_requested', $packages->get(1)->status->value);
        $this->assertSame(1, TableRegistry::getTableLocator()->get('PackagesShipRequests')->find()->where([
            'package_id' => 1,
            'ship_request_id IS NOT' => 1,
        ])->count());
    }

    public function testAddRejectsEmptyPackageSelection(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/ship-requests/add', [
            'packages' => [
                '_ids' => [],
            ],
        ]);

        $this->assertRedirect('/client/ship-requests/add');
        $this->assertFlashMessage('Select at least one package.');
    }

    public function testAddRejectsPackageThatIsNotApproved(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/ship-requests/add', [
            'packages' => [
                '_ids' => [1],
            ],
        ]);

        $this->assertRedirect('/client/ship-requests/add');
        $this->assertFlashMessage('Only approved packages can be submitted.');
        $this->assertSame('ready_to_send', TableRegistry::getTableLocator()->get('Packages')->get(1)->status->value);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Client\ShipRequestsController::edit()
     */
    public function testEdit(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/ship-requests/edit/1');

        $this->assertResponseCode(404);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Client\ShipRequestsController::delete()
     */
    public function testDelete(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/ship-requests/delete/1');

        $this->assertResponseCode(404);
    }

    private function createOtherClientShipRequest(): int
    {
        $clients = TableRegistry::getTableLocator()->get('Clients');
        $client = $clients->newEntity([
            'user_id' => 2,
            'suite_number' => 'S2A-OTHER',
        ]);
        $clients->saveOrFail($client);

        $shipRequests = TableRegistry::getTableLocator()->get('ShipRequests');
        $shipRequest = $shipRequests->newEntity([
            'client_id' => $client->id,
            'submitted_by_user_id' => 2,
            'status' => 'submitted',
            'submitted_at' => '2026-05-10 10:00:00',
        ]);
        $shipRequests->saveOrFail($shipRequest);

        return (int)$shipRequest->id;
    }
}
