<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Client;
use Authorization\IdentityInterface;

class ClientPolicy extends BasePolicy
{
    /**
     * Check view permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Client $client Client
     * @return bool
     */
    public function canView(?IdentityInterface $identity, Client $client): bool
    {
        return $this->isAdmin($identity) || $this->ownsClient($identity, $client->id);
    }

    /**
     * Check edit permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Client $client Client
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, Client $client): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check delete permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Client $client Client
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, Client $client): bool
    {
        return $this->isAdmin($identity);
    }
}
