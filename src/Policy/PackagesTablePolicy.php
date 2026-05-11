<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\PackagesTable;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class PackagesTablePolicy extends BasePolicy
{
    /**
     * Check index permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\PackagesTable $packages Packages table
     * @return bool
     */
    public function canIndex(?IdentityInterface $identity, PackagesTable $packages): bool
    {
        return $this->isAdmin($identity) || $this->isClient($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\PackagesTable $packages Packages table
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, PackagesTable $packages): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Scope package index query.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeIndex(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        if ($this->isClient($identity)) {
            return $query->where(['Packages.client_id' => $this->clientId($identity)]);
        }

        return $query;
    }

    /**
     * Scope package CSV export query.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeExportCsv(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        return $this->scopeIndex($identity, $query);
    }

    /**
     * Scope shipment query.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \Cake\ORM\Query\SelectQuery $query Query
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function scopeShipments(?IdentityInterface $identity, SelectQuery $query): SelectQuery
    {
        return $this->scopeIndex($identity, $query);
    }
}
