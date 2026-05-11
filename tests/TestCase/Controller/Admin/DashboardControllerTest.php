<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use App\Model\Enum\UserRole;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\DashboardController Test Case
 *
 * @link \App\Controller\Admin\DashboardController
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
        'app.Invoices',
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
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin');

        $this->assertResponseOk();
        $this->assertResponseContains('Admin Dashboard');
        $this->assertResponseContains('href="/admin"');
        $this->assertResponseContains('href="/admin/users"');
        $this->assertResponseContains('href="/admin/clients"');
        $this->assertResponseContains('href="/admin/packages"');
        $this->assertResponseContains('href="/admin/invoices"');
        $this->assertResponseContains('href="/admin/ship-requests"');
        $this->assertResponseContains('href="/account"');
        $this->assertResponseContains('href="/account/password"');
        $this->assertResponseContains('href="/logout"');
        $this->assertResponseNotContains('href="/client/shipments"');
    }

    public function testClientCannotAccessAdminDashboard(): void
    {
        $user = TableRegistry::getTableLocator()->get('Users')->get(1, contain: ['Clients']);
        $user->role = UserRole::Client;
        $this->session(['Auth' => $user]);

        $this->get('/admin');

        $this->assertResponseCode(403);
    }
}
