<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $hasher = new DefaultPasswordHasher();

        $this->records = [
            [
                'id' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'email' => 'admin@example.test',
                'password' => $hasher->hash('password123'),
                'password_reset_token' => null,
                'password_reset_expires' => null,
                'role' => 'admin',
                'active' => 1,
                'last_login' => '2026-05-07 22:42:31',
                'created' => 1778193751,
                'modified' => 1778193751,
            ],
            [
                'id' => 2,
                'name' => 'Client User',
                'email' => 'client@example.test',
                'password' => $hasher->hash('password123'),
                'password_reset_token' => null,
                'password_reset_expires' => null,
                'role' => 'client',
                'active' => 1,
                'last_login' => null,
                'created' => 1778193751,
                'modified' => 1778193751,
            ],
        ];
        parent::init();
    }
}
