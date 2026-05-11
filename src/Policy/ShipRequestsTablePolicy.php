<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\ShipRequestsTable;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class ShipRequestsTablePolicy extends BasePolicy
{
    /**
     * Check index permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\ShipRequestsTable $shipRequests Ship requests table
     * @return bool
     */
    public function canIndex(?IdentityInterface $identity, ShipRequestsTable $shipRequests): bool
    {
        return $this->isAdmin($identity) || $this->isClient($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\ShipRequestsTable $shipRequests Ship requests table
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, ShipRequestsTable $shipRequests): bool
    {
        return $this->isClient($identity);
    }

    /**
     * Scope ship request index query.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeIndex(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        if ($this->isClient($identity)) {
            return $query->where(['ShipRequests.client_id' => $this->clientId($identity)]);
        }

        return $query;
    }

    /**
     * Scope ship request CSV export query.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeExportCsv(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        return $this->scopeIndex($identity, $query);
    }
}
