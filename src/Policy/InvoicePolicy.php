<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Invoice;
use Authorization\IdentityInterface;

class InvoicePolicy extends BasePolicy
{
    /**
     * Check view permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canView(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return $this->isAdmin($identity) || (
            !empty($invoice->package)
            && $this->ownsClient($identity, $invoice->package->client_id)
        );
    }

    /**
     * Check approve permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canApprove(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check reject permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canReject(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check needs review permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canNeedsReview(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Check edit permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return false;
    }

    /**
     * Check delete permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Invoice $invoice Invoice
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, Invoice $invoice): bool
    {
        return false;
    }
}
