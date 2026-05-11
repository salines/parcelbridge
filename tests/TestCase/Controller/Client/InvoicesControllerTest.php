<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Client\InvoicesController Test Case
 *
 * @link \App\Controller\Client\InvoicesController
 */
class InvoicesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Invoices',
        'app.Packages',
        'app.Clients',
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Client\InvoicesController::index()
     */
    public function testIndex(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Client\InvoicesController::view()
     */
    public function testView(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testViewRejectsAnotherClientInvoice(): void
    {
        $this->createOtherClientInvoice();
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/view/2');

        $this->assertResponseCode(403);
    }

    public function testDownloadFileRedirectsWhenFileIsMissing(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/download-file/1');

        $this->assertRedirect('/client/invoices/view/1');
        $this->assertFlashMessage('The invoice file could not be found.');
    }

    public function testDownloadFileRejectsAnotherClientInvoice(): void
    {
        $this->createOtherClientInvoice();
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/download-file/2');

        $this->assertResponseCode(403);
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Client\InvoicesController::add()
     */
    public function testAdd(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/add');

        $this->assertResponseCode(404);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Client\InvoicesController::edit()
     */
    public function testEdit(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/invoices/edit/1');

        $this->assertResponseCode(404);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Client\InvoicesController::delete()
     */
    public function testDelete(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/invoices/delete/1');

        $this->assertResponseCode(404);
    }

    private function createOtherClientInvoice(): void
    {
        $clients = TableRegistry::getTableLocator()->get('Clients');
        $client = $clients->newEntity([
            'user_id' => 2,
            'suite_number' => 'S2A-OTHER',
        ]);
        $clients->saveOrFail($client);

        $packages = TableRegistry::getTableLocator()->get('Packages');
        $package = $packages->newEntity([
            'client_id' => $client->id,
            'tracking_number' => 'S2A-OTHER-PKG',
            'width' => 1,
            'height' => 1,
            'length' => 1,
            'weight' => 1,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Other client package',
            'status' => 'pending_invoice_review',
            'received_at' => '2026-05-10 10:00:00',
            'created_by_user_id' => 1,
        ]);
        $packages->saveOrFail($package);

        $invoices = TableRegistry::getTableLocator()->get('Invoices');
        $invoice = $invoices->newEntity([
            'id' => 2,
            'package_id' => $package->id,
            'uploaded_by_user_id' => 2,
            'file_path' => 'pdf/invoices/other.pdf',
            'original_filename' => 'other.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 123,
            'review_status' => 'pending',
            'uploaded_at' => '2026-05-10 10:00:00',
        ]);
        $invoices->saveOrFail($invoice);
    }
}
