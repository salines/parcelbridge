<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\ClientsTable;
use Authorization\IdentityInterface;

class ClientsTablePolicy extends BasePolicy
{
    /**
     * Check index permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\ClientsTable $clients Clients table
     * @return bool
     */
    public function canIndex(?IdentityInterface $identity, ClientsTable $clients): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\ClientsTable $clients Clients table
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, ClientsTable $clients): bool
    {
        return $this->isAdmin($identity);
    }
}
