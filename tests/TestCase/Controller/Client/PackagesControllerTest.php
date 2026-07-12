<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\UploadedFile;

/**
 * App\Controller\Client\PackagesController Test Case
 *
 * @link \App\Controller\Client\PackagesController
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
     * @link \App\Controller\Client\PackagesController::index()
     */
    public function testIndex(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    public function testExportCsv(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/export-csv?q=Lorem');

        $this->assertResponseOk();
        $this->assertContentType('text/csv');
        $this->assertHeaderContains('Content-Disposition', 'my-packages.csv');
        $this->assertResponseContains('Tracking Number');
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testDocumentPdf(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/document/1');

        $this->assertResponseOk();
        $this->assertContentType('application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'package-');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Client\PackagesController::view()
     */
    public function testView(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Related Ship Requests');
        $this->assertResponseContains('href="/client/ship-requests/view/1"');
    }

    public function testViewRejectsAnotherClientPackage(): void
    {
        $packageId = $this->createOtherClientPackage();
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/view/' . $packageId);

        $this->assertResponseCode(403);
    }

    public function testUploadInvoiceCreatesInvoiceAndTransitionsPackage(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $tmpFile = tempnam(sys_get_temp_dir(), 's2a-invoice-');
        file_put_contents($tmpFile, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF\n");

        $this->post('/client/packages/upload-invoice/1', [
            'invoice_file' => new UploadedFile($tmpFile, filesize($tmpFile), UPLOAD_ERR_OK, 'invoice.pdf', 'application/pdf'),
        ]);

        $this->assertRedirect('/client/packages/view/1');

        $invoices = TableRegistry::getTableLocator()->get('Invoices');
        $invoice = $invoices->get(1);
        $this->assertSame('invoice.pdf', $invoice->original_filename);
        $this->assertSame('pending', $invoice->review_status->value);
        $this->assertSame('pending_invoice_review', TableRegistry::getTableLocator()->get('Packages')->get(1)->status->value);

        $storedPath = RESOURCES . str_replace('/', DS, $invoice->file_path);
        $this->assertFileExists($storedPath);
        unlink($storedPath);
    }

    public function testUploadInvoiceRemovesReplacedGeneratedFile(): void
    {
        $invoices = TableRegistry::getTableLocator()->get('Invoices');
        $invoice = $invoices->get(1);
        $invoice->file_path = 'pdf/invoices/package-1-0123456789abcdef.pdf';
        $invoices->saveOrFail($invoice);
        $previousPath = RESOURCES . str_replace('/', DS, $invoice->file_path);
        file_put_contents($previousPath, "%PDF-1.4\n%%EOF\n");

        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $tmpFile = tempnam(sys_get_temp_dir(), 's2a-invoice-');
        file_put_contents($tmpFile, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF\n");
        $this->post('/client/packages/upload-invoice/1', [
            'invoice_file' => new UploadedFile($tmpFile, filesize($tmpFile), UPLOAD_ERR_OK, 'replacement.pdf', 'application/pdf'),
        ]);

        $this->assertRedirect('/client/packages/view/1');
        $this->assertFileDoesNotExist($previousPath);
        $replacement = $invoices->get(1);
        $replacementPath = RESOURCES . str_replace('/', DS, $replacement->file_path);
        $this->assertFileExists($replacementPath);
        unlink($replacementPath);
    }

    public function testUploadInvoiceRejectsInvalidMimeType(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $tmpFile = tempnam(sys_get_temp_dir(), 's2a-invoice-');
        file_put_contents($tmpFile, 'plain text');

        $this->post('/client/packages/upload-invoice/1', [
            'invoice_file' => new UploadedFile($tmpFile, filesize($tmpFile), UPLOAD_ERR_OK, 'invoice.txt', 'text/plain'),
        ]);

        $this->assertRedirect('/client/packages/upload-invoice/1');
        $this->assertFlashMessage('The invoice must be a PDF, JPG, or PNG file.');
        $this->assertFileExists($tmpFile);
        unlink($tmpFile);
    }

    public function testUploadInvoiceRejectsSpoofedClientMimeType(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $tmpFile = tempnam(sys_get_temp_dir(), 's2a-invoice-');
        file_put_contents($tmpFile, 'plain text disguised as a PDF');

        $this->post('/client/packages/upload-invoice/1', [
            'invoice_file' => new UploadedFile($tmpFile, filesize($tmpFile), UPLOAD_ERR_OK, 'invoice.pdf', 'application/pdf'),
        ]);

        $this->assertRedirect('/client/packages/upload-invoice/1');
        $this->assertFlashMessage('The invoice must be a PDF, JPG, or PNG file.');
        unlink($tmpFile);
    }

    public function testUploadInvoiceRejectsTooLargeFile(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $tmpFile = tempnam(sys_get_temp_dir(), 's2a-invoice-');
        file_put_contents($tmpFile, '%PDF oversized invoice');

        $this->post('/client/packages/upload-invoice/1', [
            'invoice_file' => new UploadedFile($tmpFile, (5 * 1024 * 1024) + 1, UPLOAD_ERR_OK, 'invoice.pdf', 'application/pdf'),
        ]);

        $this->assertRedirect('/client/packages/upload-invoice/1');
        $this->assertFlashMessage('The invoice file must be 5 MB or smaller.');
        $this->assertFileExists($tmpFile);
        unlink($tmpFile);
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Client\PackagesController::add()
     */
    public function testAdd(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/add');

        $this->assertResponseCode(404);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Client\PackagesController::edit()
     */
    public function testEdit(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/packages/edit/1');

        $this->assertResponseCode(404);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Client\PackagesController::delete()
     */
    public function testDelete(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/client/packages/delete/1');

        $this->assertResponseCode(404);
    }

    private function createOtherClientPackage(): int
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
            'status' => 'ready_to_send',
            'received_at' => '2026-05-10 10:00:00',
            'created_by_user_id' => 1,
        ]);
        $packages->saveOrFail($package);

        return (int)$package->id;
    }
}
