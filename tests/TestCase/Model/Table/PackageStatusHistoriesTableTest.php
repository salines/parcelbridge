<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackageStatusHistoriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackageStatusHistoriesTable Test Case
 */
class PackageStatusHistoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PackageStatusHistoriesTable
     */
    protected $PackageStatusHistories;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PackageStatusHistories',
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
        $config = $this->getTableLocator()->exists('PackageStatusHistories') ? [] : ['className' => PackageStatusHistoriesTable::class];
        $this->PackageStatusHistories = $this->getTableLocator()->get('PackageStatusHistories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PackageStatusHistories);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PackageStatusHistoriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $history = $this->PackageStatusHistories->newEntity([
            'package_id' => '',
            'new_status' => '',
        ]);

        $this->assertNotEmpty($history->getErrors()['package_id']);
        $this->assertNotEmpty($history->getErrors()['new_status']);
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PackageStatusHistoriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $history = $this->PackageStatusHistories->newEntity([
            'package_id' => 999,
            'changed_by_user_id' => 999,
            'changed_by_role' => 'admin',
            'old_status' => 'ready_to_send',
            'new_status' => 'pending_invoice_review',
        ]);

        $this->assertFalse($this->PackageStatusHistories->save($history));
        $this->assertArrayHasKey('package_id', $history->getErrors());
        $this->assertArrayHasKey('changed_by_user_id', $history->getErrors());
    }
}
