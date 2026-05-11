<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Client\ShipmentsController Test Case
 *
 * @link \App\Controller\Client\ShipmentsController
 */
class ShipmentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PackagesShipRequests',
        'app.ShipRequests',
        'app.Packages',
        'app.Clients',
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/client/shipments?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Search');
    }
}
