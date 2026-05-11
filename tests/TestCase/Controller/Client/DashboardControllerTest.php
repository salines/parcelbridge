<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Client;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Client\DashboardController Test Case
 *
 * @link \App\Controller\Client\DashboardController
 */
class DashboardControllerTest extends TestCase
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

        $this->get('/client');

        $this->assertResponseOk();
        $this->assertResponseContains('Dashboard');
        $this->assertResponseContains('href="/client"');
        $this->assertResponseContains('href="/client/packages"');
        $this->assertResponseContains('href="/client/invoices"');
        $this->assertResponseContains('href="/client/ship-requests"');
        $this->assertResponseContains('href="/client/shipments"');
        $this->assertResponseContains('href="/account"');
        $this->assertResponseContains('href="/account/password"');
        $this->assertResponseContains('href="/logout"');
        $this->assertResponseNotContains('href="/admin/users"');
        $this->assertResponseNotContains('href="/admin/clients"');
    }

    public function testAdminCannotAccessClientDashboard(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/client');

        $this->assertResponseCode(403);
    }
}
