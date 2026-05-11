<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Clients',
        'app.Users',
    ];

    public function testLoginUsesUsersController(): void
    {
        $this->get('/login');

        $this->assertResponseOk();
        $this->assertResponseContains('Login');
        $this->assertResponseContains('href="/login"');
        $this->assertResponseNotContains('href="/logout"');
        $this->assertResponseNotContains('href="/account"');
    }

    public function testProfileAllowsAuthenticatedUserToViewOwnAccount(): void
    {
        $this->loginAs(2);

        $this->get('/account');

        $this->assertResponseOk();
        $this->assertResponseContains('Client User');
        $this->assertResponseContains('client@example.test');
    }

    public function testForgotPasswordStoresResetToken(): void
    {
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/forgot-password', [
            'email' => 'client@example.test',
        ]);

        $this->assertRedirect('/login');

        $user = TableRegistry::getTableLocator()->get('Users')->get(2);
        $this->assertNotEmpty($user->password_reset_token);
        $this->assertNotEmpty($user->password_reset_expires);
    }

    public function testResetPasswordUpdatesPasswordAndClearsToken(): void
    {
        $token = 'reset-token';
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->get(2);
        $user->password_reset_token = hash('sha256', $token);
        $user->password_reset_expires = DateTime::now()->modify('+1 hour');
        $users->saveOrFail($user);

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/reset-password/' . $token, [
            'password' => 'reset-password',
            'password_confirm' => 'reset-password',
        ]);

        $this->assertRedirect('/login');

        $user = $users->get(2);
        $this->assertTrue(password_verify('reset-password', $user->password));
        $this->assertNull($user->password_reset_token);
        $this->assertNull($user->password_reset_expires);
    }

    public function testResetPasswordRejectsInvalidToken(): void
    {
        $this->get('/reset-password/not-a-valid-token');

        $this->assertRedirect('/login');
        $this->assertFlashMessage('The password reset link is invalid or expired.');
    }

    public function testResetPasswordRejectsMismatchedConfirmation(): void
    {
        $token = 'reset-token';
        $users = TableRegistry::getTableLocator()->get('Users');
        $user = $users->get(2);
        $originalPassword = $user->password;
        $user->password_reset_token = hash('sha256', $token);
        $user->password_reset_expires = DateTime::now()->modify('+1 hour');
        $users->saveOrFail($user);

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/reset-password/' . $token, [
            'password' => 'reset-password',
            'password_confirm' => 'different-password',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('The new password and confirmation must match.');

        $user = $users->get(2);
        $this->assertSame($originalPassword, $user->password);
        $this->assertNotNull($user->password_reset_token);
    }

    public function testEditOnlyPatchesSelfServiceFields(): void
    {
        $this->loginAs(2);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/account/edit', [
            'name' => 'Updated Client',
            'email' => 'updated-client@example.test',
            'role' => 'admin',
            'active' => 0,
        ]);

        $this->assertRedirect('/account');

        $user = TableRegistry::getTableLocator()->get('Users')->get(2);
        $this->assertSame('Updated Client', $user->name);
        $this->assertSame('updated-client@example.test', $user->email);
        $this->assertSame('client', $user->role->value);
        $this->assertTrue((bool)$user->active);
    }

    public function testChangePasswordRequiresCurrentPassword(): void
    {
        $this->loginAs(2);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirm' => 'new-password',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('The current password is not correct.');
    }

    public function testChangePasswordRejectsMismatchedConfirmation(): void
    {
        $this->loginAs(2);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/account/password', [
            'current_password' => 'password123',
            'password' => 'new-password',
            'password_confirm' => 'different-password',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('The new password and confirmation must match.');
    }

    public function testChangePasswordUpdatesPassword(): void
    {
        $this->loginAs(2);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/account/password', [
            'current_password' => 'password123',
            'password' => 'new-password',
            'password_confirm' => 'new-password',
        ]);

        $this->assertRedirect('/account');

        $user = TableRegistry::getTableLocator()->get('Users')->get(2);
        $this->assertTrue(password_verify('new-password', $user->password));
    }

    private function loginAs(int $userId): void
    {
        $this->session([
            'Auth' => TableRegistry::getTableLocator()->get('Users')->get($userId),
        ]);
    }
}
