<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPasswordResetFieldsToUsers extends BaseMigration
{
    public function change(): void
    {
        $this->table('users')
            ->addColumn('password_reset_token', 'string', [
                'after' => 'password',
                'default' => null,
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('password_reset_expires', 'datetime', [
                'after' => 'password_reset_token',
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['password_reset_token'], [
                'name' => 'idx_users_password_reset_token',
            ])
            ->update();
    }
}
