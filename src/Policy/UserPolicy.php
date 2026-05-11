<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

class UserPolicy extends BasePolicy
{
    /**
     * Check view permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\User $user User
     * @return bool
     */
    public function canView(?IdentityInterface $identity, User $user): bool
    {
        return $this->isAdmin($identity) || $this->userId($identity) === (int)$user->id;
    }

    /**
     * Check edit permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\User $user User
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, User $user): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check profile update permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\User $user User
     * @return bool
     */
    public function canUpdateProfile(?IdentityInterface $identity, User $user): bool
    {
        return $this->userId($identity) === (int)$user->id;
    }

    /**
     * Check password change permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\User $user User
     * @return bool
     */
    public function canChangePassword(?IdentityInterface $identity, User $user): bool
    {
        return $this->userId($identity) === (int)$user->id;
    }

    /**
     * Check delete permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\User $user User
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, User $user): bool
    {
        return $this->isAdmin($identity);
    }
}
