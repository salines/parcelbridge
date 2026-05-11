<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Enum\PackageStatus;
use App\Model\Enum\ShipRequestStatus;
use App\Model\Table\ShipRequestsTable;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;

/**
 * App\Model\Table\ShipRequestsTable Test Case
 */
class ShipRequestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ShipRequestsTable
     */
    protected $ShipRequests;

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
        'app.PackageStatusHistories',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ShipRequests') ? [] : ['className' => ShipRequestsTable::class];
        $this->ShipRequests = $this->getTableLocator()->get('ShipRequests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ShipRequests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ShipRequestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $shipRequest = $this->ShipRequests->newEntity([
            'client_id' => null,
            'submitted_by_user_id' => 1,
            'status' => ShipRequestStatus::Submitted->value,
            'submitted_at' => '2026-05-08 00:00:00',
        ]);

        $this->assertArrayHasKey('client_id', $shipRequest->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ShipRequestsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $shipRequest = $this->ShipRequests->newEntity([
            'client_id' => 999,
            'submitted_by_user_id' => 1,
            'status' => ShipRequestStatus::Submitted->value,
            'submitted_at' => '2026-05-08 00:00:00',
        ]);

        $this->assertFalse($this->ShipRequests->save($shipRequest));
        $this->assertArrayHasKey('client_id', $shipRequest->getErrors());
    }

    public function testSubmitForPackagesRejectsEmptySelection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Select at least one package.');

        $this->ShipRequests->submitForPackages(1, 1, []);
    }

    public function testSubmitForPackagesRejectsNonApprovedPackage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only approved packages can be submitted.');

        $this->ShipRequests->submitForPackages(1, 1, [1]);
    }

    public function testSubmitForPackagesCreatesRequestAndTransitionsPackages(): void
    {
        $package = $this->ShipRequests->PackagesShipRequests->Packages->get(1);
        $package->status = PackageStatus::InvoiceApproved;
        $this->assertNotFalse($this->ShipRequests->PackagesShipRequests->Packages->save($package));

        $shipRequest = $this->ShipRequests->submitForPackages(1, 1, [1]);

        $this->assertNotFalse($shipRequest);
        $this->assertSame(ShipRequestStatus::Submitted, $shipRequest->status);
        $this->assertSame(1, $shipRequest->client_id);
        $this->assertSame(1, $shipRequest->submitted_by_user_id);

        $package = $this->ShipRequests->PackagesShipRequests->Packages->get(1);
        $this->assertSame(PackageStatus::ShipRequested, $package->status);

        $joinTable = $this->getTableLocator()->get('PackagesShipRequests');
        $this->assertSame(1, $joinTable->find()->where([
            'package_id' => 1,
            'ship_request_id' => $shipRequest->id,
        ])->count());

        $history = $this->ShipRequests->PackagesShipRequests->Packages->PackageStatusHistories
            ->find()
            ->where([
                'package_id' => 1,
                'new_status' => PackageStatus::ShipRequested->value,
            ])
            ->first();

        $this->assertNotNull($history);
    }

    public function testSearchFinderFiltersTextIdAndStatus(): void
    {
        $this->assertSame($this->ShipRequests->find()->count(), $this->ShipRequests->find('search', search: ['q' => ''])->count());
        $this->assertSame(1, $this->ShipRequests->find('search', search: ['q' => 'Lorem ipsum'])->count());
        $this->assertSame(1, $this->ShipRequests->find('search', search: ['q' => '1'])->count());
        $this->assertSame(1, $this->ShipRequests->find('search', search: ['q' => 'Submitted'])->count());
        $this->assertSame(0, $this->ShipRequests->find('search', search: ['q' => 'No matching request'])->count());
    }
}
