<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ClientsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ClientsTable Test Case
 */
class ClientsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ClientsTable
     */
    protected $Clients;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Clients',
        'app.Users',
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
        $config = $this->getTableLocator()->exists('Clients') ? [] : ['className' => ClientsTable::class];
        $this->Clients = $this->getTableLocator()->get('Clients', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Clients);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ClientsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $client = $this->Clients->newEntity([
            'user_id' => '',
            'suite_number' => '',
        ]);

        $this->assertNotEmpty($client->getErrors()['user_id']);
        $this->assertNotEmpty($client->getErrors()['suite_number']);
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ClientsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $duplicate = $this->Clients->newEntity([
            'user_id' => 2,
            'suite_number' => 'Lorem ipsum dolor sit amet',
        ]);
        $this->assertFalse($this->Clients->save($duplicate));
        $this->assertArrayHasKey('suite_number', $duplicate->getErrors());

        $invalidUser = $this->Clients->newEntity([
            'user_id' => 999,
            'suite_number' => 'S2A-MISSING-USER',
        ]);
        $this->assertFalse($this->Clients->save($invalidUser));
        $this->assertArrayHasKey('user_id', $invalidUser->getErrors());
    }

    public function testSearchFinderFiltersClientAndUserText(): void
    {
        $this->assertSame($this->Clients->find()->count(), $this->Clients->find('search', search: ['q' => ''])->count());
        $this->assertSame(1, $this->Clients->find('search', search: ['q' => 'Lorem ipsum'])->count());
        $this->assertSame(1, $this->Clients->find('search', search: ['q' => 'dolor sit'])->count());
        $this->assertSame(0, $this->Clients->find('search', search: ['q' => 'No matching client'])->count());
    }
}
