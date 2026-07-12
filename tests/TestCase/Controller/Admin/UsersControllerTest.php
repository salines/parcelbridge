<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Admin;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Admin\UsersController Test Case
 *
 * @link \App\Controller\Admin\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\Admin\UsersController::index()
     */
    public function testIndex(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/users?q=Lorem');

        $this->assertResponseOk();
        $this->assertResponseContains('Lorem ipsum');
        $this->assertResponseContains('Search');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\Admin\UsersController::view()
     */
    public function testView(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);

        $this->get('/admin/users/view/1');

        $this->assertResponseOk();
        $this->assertResponseContains('admin@example.test');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\Admin\UsersController::add()
     */
    public function testAdd(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/users/add', [
            'name' => 'New Client',
            'email' => 'new-client@example.test',
            'password' => 'password1234',
            'role' => 'client',
            'active' => 1,
        ]);

        $this->assertRedirect('/admin/users');
        $this->assertSame(1, TableRegistry::getTableLocator()->get('Users')->find()->where([
            'email' => 'new-client@example.test',
        ])->count());
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\Admin\UsersController::edit()
     */
    public function testEdit(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/users/edit/2', [
            'name' => 'Edited Client',
            'email' => 'edited-client@example.test',
            'role' => 'client',
            'active' => 1,
        ]);

        $this->assertRedirect('/admin/users');
        $user = TableRegistry::getTableLocator()->get('Users')->get(2);
        $this->assertSame('Edited Client', $user->name);
        $this->assertSame('edited-client@example.test', $user->email);
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\Admin\UsersController::delete()
     */
    public function testDelete(): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get(1),
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/admin/users/delete/2');

        $this->assertRedirect('/admin/users');
        $this->assertFalse(TableRegistry::getTableLocator()->get('Users')->exists(['id' => 2]));
    }
}
