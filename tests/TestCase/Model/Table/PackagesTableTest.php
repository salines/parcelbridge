<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Enum\PackageStatus;
use App\Model\Enum\UserRole;
use App\Model\Table\PackagesTable;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * App\Model\Table\PackagesTable Test Case
 */
class PackagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PackagesTable
     */
    protected $Packages;

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
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Packages') ? [] : ['className' => PackagesTable::class];
        $this->Packages = $this->getTableLocator()->get('Packages', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Packages);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PackagesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $package = $this->Packages->newEntity([
            'client_id' => 1,
            'tracking_number' => '',
            'width' => 1,
            'height' => 1,
            'length' => 1,
            'weight' => 1,
            'contents_description' => 'Test package',
            'status' => PackageStatus::ReadyToSend->value,
            'received_at' => '2026-05-08 00:00:00',
        ]);

        $this->assertArrayHasKey('tracking_number', $package->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PackagesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $package = $this->Packages->newEntity([
            'client_id' => 1,
            'tracking_number' => 'Lorem ipsum dolor sit amet',
            'width' => 1,
            'height' => 1,
            'length' => 1,
            'weight' => 1,
            'dimension_unit' => 'in',
            'weight_unit' => 'lb',
            'contents_description' => 'Duplicate tracking number',
            'status' => PackageStatus::ReadyToSend->value,
            'received_at' => '2026-05-08 00:00:00',
            'created_by_user_id' => 1,
        ]);

        $this->assertFalse($this->Packages->save($package));
        $this->assertArrayHasKey('tracking_number', $package->getErrors());
    }

    public function testSearchFinderFiltersTextAndStatus(): void
    {
        $this->assertSame($this->Packages->find()->count(), $this->Packages->find('search', search: ['q' => ''])->count());
        $this->assertSame(1, $this->Packages->find('search', search: ['q' => 'Lorem ipsum'])->count());
        $this->assertSame(1, $this->Packages->find('search', search: ['q' => 'dolor sit'])->count());
        $this->assertSame(1, $this->Packages->find('search', search: ['q' => 'Ready to Send'])->count());
        $this->assertSame(0, $this->Packages->find('search', search: ['q' => 'No matching package'])->count());
    }

    public function testTransitionStatusFollowsMvpWorkflow(): void
    {
        $package = $this->Packages->get(1);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::PendingInvoiceReview,
            1,
            UserRole::Client,
            'Invoice uploaded',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::PendingInvoiceReview, $package->status);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::InvoiceApproved,
            1,
            UserRole::Admin,
            'Invoice approved',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::InvoiceApproved, $package->status);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::ShipRequested,
            1,
            UserRole::Client,
            'Ship request submitted',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::ShipRequested, $package->status);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::Shipped,
            1,
            UserRole::Admin,
            'Ship request processed',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::Shipped, $package->status);
        $this->assertNotNull($package->shipped_at);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::ReadyForPickup,
            1,
            UserRole::Admin,
            'Arrived at destination',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::ReadyForPickup, $package->status);
        $this->assertNotNull($package->ready_for_pickup_at);

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::Delivered,
            1,
            UserRole::Admin,
            'Delivered',
        ));

        $package = $this->Packages->get(1);
        $this->assertSame(PackageStatus::Delivered, $package->status);
        $this->assertNotNull($package->delivered_at);
    }

    public function testTransitionStatusRejectsSkippedWorkflowStep(): void
    {
        $package = $this->Packages->get(1);
        $beforeCount = $this->Packages->PackageStatusHistories->find()->count();

        $this->expectException(InvalidArgumentException::class);
        try {
            $this->Packages->transitionStatus(
                $package,
                PackageStatus::ShipRequested,
                1,
                UserRole::Client,
                'Invalid transition',
            );
        } finally {
            $this->assertSame(PackageStatus::ReadyToSend, $this->Packages->get(1)->status);
            $this->assertSame($beforeCount, $this->Packages->PackageStatusHistories->find()->count());
        }
    }

    public function testTransitionStatusRecordsAuditHistory(): void
    {
        $package = $this->Packages->get(1);
        $beforeCount = $this->Packages->PackageStatusHistories->find()->count();

        $this->assertTrue($this->Packages->transitionStatus(
            $package,
            PackageStatus::PendingInvoiceReview,
            1,
            UserRole::Client,
            'Invoice uploaded',
        ));

        $history = $this->Packages->PackageStatusHistories
            ->find()
            ->where(['package_id' => 1])
            ->orderByDesc('id')
            ->firstOrFail();

        $this->assertSame($beforeCount + 1, $this->Packages->PackageStatusHistories->find()->count());
        $this->assertSame(PackageStatus::ReadyToSend, $history->old_status);
        $this->assertSame(PackageStatus::PendingInvoiceReview, $history->new_status);
        $this->assertSame(UserRole::Client, $history->changed_by_role);
        $this->assertSame('Invoice uploaded', $history->note);
    }
}
