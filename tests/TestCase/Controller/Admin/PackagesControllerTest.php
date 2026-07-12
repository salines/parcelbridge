<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\PackagesController Test Case
 *
 * @link \App\Controller\Admin\PackagesController
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

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Admin\PackagesController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/packages?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    public function testExportCsv(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/packages/export-csv?q=Lorem');

        $this->assertResponseOk();
        $this->assertContentType('text/csv');
        $this->assertHeaderContains('Content-Disposition', 'packages.csv');
        $this->assertResponseContains('Tracking Number');
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testDocumentPdf(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/packages/document/1');

        $this->assertResponseOk();
        $this->assertContentType('application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'package-');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Admin\PackagesController::view()
     */
    public function testView(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/packages/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Related Ship Requests');
        $this->assertResponseContains('href="/admin/ship-requests/view/1"');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Admin\PackagesController::add()
     */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/packages/add', [
            'client_id' => 1,
            'tracking_number' => 'S2A-ADD-001',
            'width' => 10,
            'height' => 8,
            'length' => 12,
            'weight' => 4,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Added package',
            'received_at' => '2026-05-10 10:00:00',
        ]);

        $this->assertRedirect('/admin/packages');
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $package = $packages->find()->where(['tracking_number' => 'S2A-ADD-001'])->firstOrFail();
        $this->assertSame('ready_to_send', $package->status->value);
        $this->assertSame(1, $packages->PackageStatusHistories->find()->where([
            'package_id' => $package->id,
        ])->count());
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Admin\PackagesController::edit()
     */
    public function testEdit(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/packages/edit/1', [
            'client_id' => 1,
            'tracking_number' => 'S2A-EDIT-001',
            'width' => 11,
            'height' => 9,
            'length' => 13,
            'weight' => 5,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Edited package',
            'status' => 'delivered',
        ]);

        $this->assertRedirect('/admin/packages');
        $package = TableRegistry::getTableLocator()->get('Packages')->get(1);
        $this->assertSame('S2A-EDIT-001', $package->tracking_number);
        $this->assertSame('ready_to_send', $package->status->value);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Admin\PackagesController::delete()
     */
    public function testDelete(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $package = $packages->newEntity([
            'client_id' => 1,
            'tracking_number' => 'S2A-DELETE-001',
            'width' => 10,
            'height' => 8,
            'length' => 12,
            'weight' => 4,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Delete package',
            'status' => 'ready_to_send',
            'received_at' => '2026-05-10 10:00:00',
            'created_by_user_id' => 1,
        ]);
        $packages->saveOrFail($package);

        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/packages/delete/' . $package->id);

        $this->assertRedirect('/admin/packages');
        $this->assertFalse($packages->exists(['id' => $package->id]));
    }

    public function testDeleteRejectsPackageWithOperationalRecords(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/packages/delete/1');

        $this->assertRedirect('/admin/packages');
        $this->assertFlashMessage('This package cannot be deleted because it has related operational records.');
        $this->assertTrue($packages->exists(['id' => 1]));
    }
}
