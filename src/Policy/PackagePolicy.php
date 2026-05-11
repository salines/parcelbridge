<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Package;
use Authorization\IdentityInterface;

class PackagePolicy extends BasePolicy
{
    /**
     * Check view permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canView(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity) || $this->ownsClient($identity, $package->client_id);
    }

    /**
     * Check invoice upload permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canUploadInvoice(?IdentityInterface $identity, Package $package): bool
    {
        if (!$this->ownsClient($identity, $package->client_id)) {
            return false;
        }

        return $package->canUploadInvoice();
    }

    /**
     * Check add permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canAdd(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check edit permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check delete permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check ready-for-pickup permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canReadyForPickup(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity);
    }

    /**
     * Check delivery permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\Package $package Package
     * @return bool
     */
    public function canDeliver(?IdentityInterface $identity, Package $package): bool
    {
        return $this->isAdmin($identity);
    }
}
