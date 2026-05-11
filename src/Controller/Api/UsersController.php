<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Event\EventInterface;

/**
 * API Users Controller.
 *
 * @property \App\Model\Table\UsersTable $Users
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

        $this->Authentication->allowUnauthenticated(['login']);
        if (in_array($this->request->getParam('action'), ['login', 'logout'], true)) {
            $this->Authorization->skipAuthorization();
        }
    }

    /**
     * Login through API.
     *
     * @return void
     */
    public function login(): void
    {
        $this->request->allowMethod(['post']);

        $result = $this->Authentication->getResult();
        if ($result === null || !$result->isValid()) {
            $this->jsonError(__('Invalid email or password.'), 401);

            return;
        }

        $user = $this->Users->get($this->Authentication->getIdentifier(), contain: ['Clients']);
        $this->json(['user' => $this->userResource($user)]);
    }

    /**
     * Current authenticated user.
     *
     * @return void
     */
    public function me(): void
    {
        $user = $this->Users->get($this->Authentication->getIdentifier(), contain: ['Clients']);
        $this->Authorization->authorize($user, 'view');

        $this->json(['user' => $this->userResource($user)]);
    }

    /**
     * Logout through API.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->Authentication->logout();

        $this->json(['success' => true]);
    }
}
