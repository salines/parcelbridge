<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\ClientsController Test Case
 *
 * @link \App\Controller\Admin\ClientsController
 */
class ClientsControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
     * Test index method
     *
     * @return void
     * @link \App\Controller\Admin\ClientsController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/clients?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Admin\ClientsController::view()
     */
    public function testView(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/clients/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Admin\ClientsController::add()
     */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/clients/add', [
            'user_id' => 2,
            'suite_number' => 'S2A-2002',
            'phone' => '+297 555 0202',
            'address_line_1' => 'Address 1',
            'city' => 'Oranjestad',
            'country' => 'Destination',
        ]);

        $this->assertRedirect('/admin/clients');
        $this->assertSame(1, TableRegistry::getTableLocator()->get('Clients')->find()->where([
            'suite_number' => 'S2A-2002',
        ])->count());
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Admin\ClientsController::edit()
     */
    public function testEdit(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/clients/edit/1', [
            'user_id' => 1,
            'suite_number' => 'S2A-EDIT',
            'phone' => '+297 555 0303',
            'address_line_1' => 'Edited address',
            'city' => 'Oranjestad',
            'country' => 'Destination',
        ]);

        $this->assertRedirect('/admin/clients');
        $client = TableRegistry::getTableLocator()->get('Clients')->get(1);
        $this->assertSame('S2A-EDIT', $client->suite_number);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Admin\ClientsController::delete()
     */
    public function testDelete(): void
    {
        $clients = TableRegistry::getTableLocator()->get('Clients');
        $client = $clients->newEntity([
            'user_id' => 2,
            'suite_number' => 'S2A-DELETE',
        ]);
        $clients->saveOrFail($client);

        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/clients/delete/' . $client->id);

        $this->assertRedirect('/admin/clients');
        $this->assertFalse($clients->exists(['id' => $client->id]));
    }
}
