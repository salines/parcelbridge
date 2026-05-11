<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Enum\UserRole;
use Authorization\IdentityInterface;
use Cake\Datasource\EntityInterface;

abstract class BasePolicy
{
    /**
     * Check whether identity has admin role.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return bool
     */
    protected function isAdmin(?IdentityInterface $identity): bool
    {
        return $this->role($identity) === UserRole::Admin->value;
    }

    /**
     * Check whether identity has client role.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return bool
     */
    protected function isClient(?IdentityInterface $identity): bool
    {
        return $this->role($identity) === UserRole::Client->value;
    }

    /**
     * Current identity user id.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return int|null
     */
    protected function userId(?IdentityInterface $identity): ?int
    {
        $user = $this->user($identity);

        return $user && isset($user->id) ? (int)$user->id : null;
    }

    /**
     * Current identity client id.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return int|null
     */
    protected function clientId(?IdentityInterface $identity): ?int
    {
        $user = $this->user($identity);
        if (!$user || empty($user->client)) {
            return null;
        }

        return isset($user->client->id) ? (int)$user->client->id : null;
    }

    /**
     * Check whether identity owns a client record.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param string|int|null $clientId Client id
     * @return bool
     */
    protected function ownsClient(?IdentityInterface $identity, int|string|null $clientId): bool
    {
        return $clientId !== null && $this->clientId($identity) === (int)$clientId;
    }

    /**
     * Resolve identity role value.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return string|null
     */
    private function role(?IdentityInterface $identity): ?string
    {
        $user = $this->user($identity);
        $role = $user?->get('role');
        if ($role instanceof UserRole) {
            return $role->value;
        }

        return is_string($role) ? $role : null;
    }

    /**
     * Resolve identity user entity.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @return \Cake\Datasource\EntityInterface|null
     */
    private function user(?IdentityInterface $identity): ?EntityInterface
    {
        if ($identity === null) {
            return null;
        }

        $user = $identity->getOriginalData();

        return $user instanceof EntityInterface ? $user : null;
    }
}
