<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InvoicesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InvoicesTable Test Case
 */
class InvoicesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InvoicesTable
     */
    protected $Invoices;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Invoices',
        'app.Packages',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Invoices') ? [] : ['className' => InvoicesTable::class];
        $this->Invoices = $this->getTableLocator()->get('Invoices', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Invoices);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\InvoicesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $invoice = $this->Invoices->newEntity([
            'package_id' => '',
            'uploaded_by_user_id' => '',
            'file_path' => '',
            'original_filename' => '',
            'mime_type' => '',
            'file_size' => '',
            'uploaded_at' => '',
        ]);

        $this->assertNotEmpty($invoice->getErrors()['package_id']);
        $this->assertNotEmpty($invoice->getErrors()['uploaded_by_user_id']);
        $this->assertNotEmpty($invoice->getErrors()['file_path']);
        $this->assertNotEmpty($invoice->getErrors()['original_filename']);
        $this->assertNotEmpty($invoice->getErrors()['mime_type']);
        $this->assertNotEmpty($invoice->getErrors()['file_size']);
        $this->assertNotEmpty($invoice->getErrors()['uploaded_at']);
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\InvoicesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $duplicate = $this->Invoices->newEntity([
            'package_id' => 1,
            'uploaded_by_user_id' => 1,
            'file_path' => 'pdf/invoices/duplicate.pdf',
            'original_filename' => 'duplicate.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 123,
            'review_status' => 'pending',
            'uploaded_at' => '2026-05-10 10:00:00',
        ]);
        $this->assertFalse($this->Invoices->save($duplicate));
        $this->assertArrayHasKey('package_id', $duplicate->getErrors());

        $invalidUser = $this->Invoices->newEntity([
            'package_id' => 999,
            'uploaded_by_user_id' => 999,
            'file_path' => 'pdf/invoices/missing.pdf',
            'original_filename' => 'missing.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 123,
            'review_status' => 'pending',
            'uploaded_at' => '2026-05-10 10:00:00',
        ]);
        $this->assertFalse($this->Invoices->save($invalidUser));
        $this->assertArrayHasKey('uploaded_by_user_id', $invalidUser->getErrors());
    }

    public function testSearchFinderFiltersTextAndReviewStatus(): void
    {
        $this->assertSame($this->Invoices->find()->count(), $this->Invoices->find('search', search: ['q' => ''])->count());
        $this->assertSame(1, $this->Invoices->find('search', search: ['q' => 'Lorem ipsum'])->count());
        $this->assertSame(1, $this->Invoices->find('search', search: ['q' => 'Pending'])->count());
        $this->assertSame(0, $this->Invoices->find('search', search: ['q' => 'No matching invoice'])->count());
    }
}
