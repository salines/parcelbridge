<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersTable Test Case
 */
class UsersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Clients',
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
        $config = $this->getTableLocator()->exists('Users') ? [] : ['className' => UsersTable::class];
        $this->Users = $this->getTableLocator()->get('Users', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Users);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\UsersTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $user = $this->Users->newEntity([]);

        $this->assertNotEmpty($user->getErrors()['name']);
        $this->assertNotEmpty($user->getErrors()['email']);
        $this->assertNotEmpty($user->getErrors()['password']);
        $this->assertNotEmpty($user->getErrors()['role']);
    }

    public function testValidationRejectsShortPassword(): void
    {
        $user = $this->Users->newEntity([
            'name' => 'Short Password',
            'email' => 'short-password@example.test',
            'password' => 'short7!',
            'role' => 'client',
            'active' => true,
        ]);

        $this->assertArrayHasKey('password', $user->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\UsersTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $user = $this->Users->newEntity([
            'name' => 'Duplicate Email',
            'email' => 'admin@example.test',
            'password' => 'password1234',
            'role' => 'admin',
            'active' => 1,
        ]);

        $this->assertFalse($this->Users->save($user));
        $this->assertArrayHasKey('email', $user->getErrors());
    }

    public function testSearchFinderFiltersTextAndRole(): void
    {
        $this->assertSame($this->Users->find()->count(), $this->Users->find('search', search: ['q' => ''])->count());
        $this->assertSame(1, $this->Users->find('search', search: ['q' => 'Lorem ipsum'])->count());
        $this->assertSame(1, $this->Users->find('search', search: ['q' => 'admin@example.test'])->count());
        $this->assertSame(1, $this->Users->find('search', search: ['q' => 'Admin'])->count());
        $this->assertSame(0, $this->Users->find('search', search: ['q' => 'No matching user'])->count());
    }
}
