<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\InvoicesController Test Case
 *
 * @link \App\Controller\Admin\InvoicesController
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
        'app.PackageStatusHistories',
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Admin\InvoicesController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/invoices?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Admin\InvoicesController::view()
     */
    public function testView(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/invoices/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
    }

    public function testDownloadFileRedirectsWhenFileIsMissing(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/invoices/download-file/1');

        $this->assertRedirect('/admin/invoices/view/1');
        $this->assertFlashMessage('The invoice file could not be found.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Admin\InvoicesController::add()
     */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/invoices/add');

        $this->assertResponseCode(404);
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Admin\InvoicesController::edit()
     */
    public function testEdit(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/invoices/edit/1');

        $this->assertResponseCode(404);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Admin\InvoicesController::delete()
     */
    public function testDelete(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/invoices/delete/1');

        $this->assertResponseCode(404);
    }

    public function testApproveRejectsPackageThatIsNotPendingInvoiceReview(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/invoices/approve/1');

        $this->assertRedirect('/admin/invoices/view/1');
        $this->assertFlashMessage('Only packages pending invoice review can be approved.');
        $invoice = TableRegistry::getTableLocator()->get('Invoices')->get(1);
        $this->assertSame('pending', $invoice->review_status->value);
    }

    public function testApproveUpdatesInvoicePackageAndHistory(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $packages->updateAll(['status' => 'pending_invoice_review'], ['id' => 1]);

        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/invoices/approve/1');

        $this->assertRedirect('/admin/invoices');

        $invoice = TableRegistry::getTableLocator()->get('Invoices')->get(1);
        $this->assertSame('approved', $invoice->review_status->value);
        $this->assertSame('invoice_approved', $packages->get(1)->status->value);
        $this->assertSame(1, $packages->PackageStatusHistories->find()->where([
            'package_id' => 1,
            'new_status' => 'invoice_approved',
        ])->count());
    }

    public function testNeedsReviewRequiresNote(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/invoices/needs-review/1', [
            'admin_notes' => '',
        ]);

        $this->assertRedirect('/admin/invoices/view/1');
        $this->assertFlashMessage('Enter a review note before marking the invoice for review.');
    }

    public function testNeedsReviewUpdatesInvoicePackageAndHistory(): void
    {
        $packages = TableRegistry::getTableLocator()->get('Packages');
        $packages->updateAll(['status' => 'pending_invoice_review'], ['id' => 1]);

        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/invoices/needs-review/1', [
            'admin_notes' => 'Missing declared value.',
        ]);

        $this->assertRedirect('/admin/invoices');

        $invoice = TableRegistry::getTableLocator()->get('Invoices')->get(1);
        $this->assertSame('needs_review', $invoice->review_status->value);
        $this->assertSame('Missing declared value.', $invoice->admin_notes);
        $this->assertSame('needs_review', $packages->get(1)->status->value);
        $this->assertSame(1, $packages->PackageStatusHistories->find()->where([
            'package_id' => 1,
            'new_status' => 'needs_review',
            'note' => 'Missing declared value.',
        ])->count());
    }
}
