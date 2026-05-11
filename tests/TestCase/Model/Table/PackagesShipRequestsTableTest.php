<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PackagesShipRequestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PackagesShipRequestsTable Test Case
 */
class PackagesShipRequestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PackagesShipRequestsTable
     */
    protected $PackagesShipRequests;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PackagesShipRequests',
        'app.Packages',
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
        $config = $this->getTableLocator()->exists('PackagesShipRequests') ? [] : ['className' => PackagesShipRequestsTable::class];
        $this->PackagesShipRequests = $this->getTableLocator()->get('PackagesShipRequests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PackagesShipRequests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PackagesShipRequestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $join = $this->PackagesShipRequests->newEntity([
            'package_id' => '',
            'ship_request_id' => '',
        ]);

        $this->assertNotEmpty($join->getErrors()['package_id']);
        $this->assertNotEmpty($join->getErrors()['ship_request_id']);
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PackagesShipRequestsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $duplicate = $this->PackagesShipRequests->newEntity([
            'package_id' => 1,
            'ship_request_id' => 1,
        ]);
        $this->assertFalse($this->PackagesShipRequests->save($duplicate));
        $this->assertArrayHasKey('package_id', $duplicate->getErrors());

        $missing = $this->PackagesShipRequests->newEntity([
            'package_id' => 999,
            'ship_request_id' => 999,
        ]);
        $this->assertFalse($this->PackagesShipRequests->save($missing));
        $this->assertArrayHasKey('package_id', $missing->getErrors());
        $this->assertArrayHasKey('ship_request_id', $missing->getErrors());
    }
}
