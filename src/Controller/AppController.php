<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Enum\UserRole;
use Authentication\Controller\Component\AuthenticationComponent;
use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 * @property \Authentication\Controller\Component\AuthenticationComponent $Authentication
 * @property \Authorization\Controller\Component\AuthorizationComponent $Authorization
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Set layout-level authentication context.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event Event
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $identity = null;
        if ($this->components()->has('Authentication')) {
            $component = $this->components()->get('Authentication');
            if ($component instanceof AuthenticationComponent) {
                $identity = $component->getIdentity();
            }
        }

        $user = $identity?->getOriginalData();
        $role = $user instanceof EntityInterface ? $user->get('role') : null;
        if ($role instanceof UserRole) {
            $role = $role->value;
        }

        $this->set('currentUserRole', is_string($role) ? $role : null);
    }

    /**
     * Current authenticated user id.
     *
     * @return int|null
     */
    protected function currentUserId(): ?int
    {
        $identifier = $this->Authentication->getIdentifier();

        return is_numeric($identifier) ? (int)$identifier : null;
    }

    /**
     * Current authenticated user's client id.
     *
     * @return int|null
     */
    protected function currentClientId(): ?int
    {
        $userId = $this->currentUserId();
        if ($userId === null) {
            return null;
        }

        $client = $this->fetchTable('Clients')
            ->find()
            ->select(['id'])
            ->where(['Clients.user_id' => $userId])
            ->first();

        $clientId = $client?->get('id');

        return $clientId === null ? null : (int)$clientId;
    }
}
