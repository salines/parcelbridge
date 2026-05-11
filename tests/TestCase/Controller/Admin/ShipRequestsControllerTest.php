<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\ShipRequestsController Test Case
 *
 * @link \App\Controller\Admin\ShipRequestsController
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
        'app.ShipRequests',
        'app.Clients',
        'app.Users',
        'app.Packages',
        'app.PackagesShipRequests',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Admin\ShipRequestsController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    public function testExportCsv(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests/export-csv?q=Lorem');

        $this->assertResponseOk();
        $this->assertContentType('text/csv');
        $this->assertHeaderContains('Content-Disposition', 'ship-requests.csv');
        $this->assertResponseContains('Processing Reference');
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testManifestPdf(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests/manifest/1');

        $this->assertResponseOk();
        $this->assertContentType('application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'ship-request-1-manifest.pdf');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Admin\ShipRequestsController::view()
     */
    public function testView(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Ship Request');
        $this->assertResponseContains('Related Packages');
        $this->assertResponseContains('href="/admin/packages/view/1"');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Admin\ShipRequestsController::add()
     */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests/add');

        $this->assertResponseCode(404);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Admin\ShipRequestsController::edit()
     */
    public function testEdit(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/ship-requests/edit/1');

        $this->assertResponseCode(404);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Admin\ShipRequestsController::delete()
     */
    public function testDelete(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/ship-requests/delete/1');

        $this->assertResponseCode(404);
    }

    public function testProcessRejectsPackagesThatAreNotShipRequested(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/ship-requests/process/1', [
            'processing_reference' => 'PROC-001',
        ]);

        $this->assertRedirect('/admin/ship-requests/view/1');
        $this->assertFlashMessage('All packages must be ready for shipment before processing.');
        $shipRequest = TableRegistry::getTableLocator()->get('ShipRequests')->get(1);
        $this->assertSame('submitted', $shipRequest->status->value);
    }
}
