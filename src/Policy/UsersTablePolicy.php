<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\UsersTable;
use Authorization\IdentityInterface;

class UsersTablePolicy extends BasePolicy
{
    /**
     * Check index permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\UsersTable $users Users table
     * @return bool
     */
    public function canIndex(?IdentityInterface $identity, UsersTable $users): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\UsersTable $users Users table
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, UsersTable $users): bool
    {
        return $this->isAdmin($identity);
    }
}
