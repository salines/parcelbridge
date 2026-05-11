<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Table\InvoicesTable;
use Authorization\IdentityInterface;
use Cake\ORM\Query\SelectQuery;

class InvoicesTablePolicy extends BasePolicy
{
    /**
     * Check index permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\InvoicesTable $invoices Invoices table
     * @return bool
     */
    public function canIndex(?IdentityInterface $identity, InvoicesTable $invoices): bool
    {
        return $this->isAdmin($identity) || $this->isClient($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Table\InvoicesTable $invoices Invoices table
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, InvoicesTable $invoices): bool
    {
        return false;
    }

    /**
     * Scope invoice index query.
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
}
