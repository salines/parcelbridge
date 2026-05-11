<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Enum\UserRole;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\I18n\DateTime;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;
use Throwable;

/**
 * Users Controller
 *
 * Handles public authentication and authenticated account self-service.
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class UsersController extends AppController
{
    /**
     * Before filter.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'forgotPassword', 'resetPassword']);
        if (in_array($this->request->getParam('action'), ['login', 'logout', 'forgotPassword', 'resetPassword'], true)) {
            $this->Authorization->skipAuthorization();
        }
    }

    /**
     * Login action.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);

        $result = $this->Authentication->getResult();

        if ($result !== null && $result->isValid()) {
            return $this->Authentication->redirectAfterLogin($this->dashboardUrl());
        }

        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid email or password.'));
        }
    }

    /**
     * Logout action.
     *
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
        $this->request->allowMethod(['get', 'post']);

        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Start password reset flow.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function forgotPassword()
    {
        $this->request->allowMethod(['get', 'post']);

        if ($this->request->is('post')) {
            $email = trim((string)$this->request->getData('email'));
            $user = $this->Users
                ->find()
                ->where([
                    'email' => $email,
                    'active' => true,
                ])
                ->first();

            if ($user !== null) {
                $token = bin2hex(random_bytes(32));
                $user->password_reset_token = hash('sha256', $token);
                $user->password_reset_expires = DateTime::now()->modify('+1 hour');

                if ($this->Users->save($user)) {
                    $this->sendPasswordResetEmail($user, $token);
                }
            }

            $this->Flash->success(__('If the account exists, password reset instructions have been sent.'));

            return $this->redirect(['action' => 'login']);
        }
    }

    /**
     * Complete password reset flow.
     *
     * @param string $token Password reset token
     * @return \Cake\Http\Response|null|void
     */
    public function resetPassword(string $token)
    {
        $this->request->allowMethod(['get', 'post']);

        $user = null;
        if ($token !== '') {
            $user = $this->Users
                ->find()
                ->where([
                    'password_reset_token' => hash('sha256', $token),
                    'password_reset_expires >=' => DateTime::now(),
                    'active' => true,
                ])
                ->first();
        }

        if (!$user) {
            $this->Flash->error(__('The password reset link is invalid or expired.'));

            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $password = (string)$this->request->getData('password');
            $passwordConfirm = (string)$this->request->getData('password_confirm');

            if ($password === '' || $password !== $passwordConfirm) {
                $this->Flash->error(__('The new password and confirmation must match.'));
            } else {
                $user = $this->Users->patchEntity($user, ['password' => $password], [
                    'fields' => ['password'],
                ]);
                $user->password_reset_token = null;
                $user->password_reset_expires = null;

                if ($this->Users->save($user)) {
                    $this->Flash->success(__('Your password has been reset. You can now log in.'));

                    return $this->redirect(['action' => 'login']);
                }

                $this->Flash->error(__('Your password could not be reset. Please try again.'));
            }
        }

        $this->set(compact('token'));
    }

    /**
     * Account profile.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function profile()
    {
        $user = $this->currentUser();
        $this->Authorization->authorize($user, 'view');

        $this->set(compact('user'));
    }

    /**
     * Edit the authenticated user's own account fields.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        $user = $this->currentUser();
        $this->Authorization->authorize($user, 'updateProfile');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'fields' => ['name', 'email'],
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Your account has been updated.'));

                return $this->redirect(['action' => 'profile']);
            }

            $this->Flash->error(__('Your account could not be updated. Please try again.'));
        }

        $this->set(compact('user'));
    }

    /**
     * Change the authenticated user's password.
     *
     * @return \Cake\Http\Response|null|void
     */
    public function changePassword()
    {
        $user = $this->currentUser();
        $this->Authorization->authorize($user, 'changePassword');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $currentPassword = (string)$this->request->getData('current_password');
            $password = (string)$this->request->getData('password');
            $passwordConfirm = (string)$this->request->getData('password_confirm');

            if (!(new DefaultPasswordHasher())->check($currentPassword, $user->password)) {
                $this->Flash->error(__('The current password is not correct.'));
            } elseif ($password === '' || $password !== $passwordConfirm) {
                $this->Flash->error(__('The new password and confirmation must match.'));
            } else {
                $user = $this->Users->patchEntity($user, ['password' => $password], [
                    'fields' => ['password'],
                ]);

                if ($this->Users->save($user)) {
                    $this->Flash->success(__('Your password has been changed.'));

                    return $this->redirect(['action' => 'profile']);
                }

                $this->Flash->error(__('Your password could not be changed. Please try again.'));
            }
        }

        $this->set(compact('user'));
    }

    /**
     * Authenticated user entity.
     *
     * @return \App\Model\Entity\User
     */
    private function currentUser(): User
    {
        return $this->Users->get($this->Authentication->getIdentity()?->getIdentifier(), contain: ['Clients']);
    }

    /**
     * Send password reset email.
     *
     * @param \App\Model\Entity\User $user User
     * @param string $token Plain reset token
     * @return void
     */
    private function sendPasswordResetEmail(User $user, string $token): void
    {
        $resetUrl = Router::url(['controller' => 'Users', 'action' => 'resetPassword', $token], true);

        try {
            (new Mailer('default'))
                ->setTo($user->email)
                ->setSubject(__('Reset your ParcelBridge password'))
                ->deliver(__(
                    "Use this link to reset your ParcelBridge password:\n\n{0}\n\nThis link expires in one hour.",
                    $resetUrl,
                ));
        } catch (Throwable) {
            $this->log(sprintf('Password reset email could not be sent to user %d.', $user->id), 'warning');
        }
    }

    /**
     * Dashboard URL for the authenticated user's role.
     *
     * @return array<string, mixed>
     */
    private function dashboardUrl(): array
    {
        $identity = $this->Authentication->getIdentity();
        $user = $identity?->getOriginalData();
        $role = $user instanceof EntityInterface ? $user->get('role') : null;

        if ($role === UserRole::Admin || $role === UserRole::Admin->value) {
            return ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'];
        }

        return ['prefix' => 'Client', 'controller' => 'Dashboard', 'action' => 'index'];
    }
}
