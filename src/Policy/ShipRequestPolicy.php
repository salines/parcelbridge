<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\ShipRequest;
use Authorization\IdentityInterface;

class ShipRequestPolicy extends BasePolicy
{
    /**
     * Check view permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\ShipRequest $shipRequest Ship request
     * @return bool
     */
    public function canView(?IdentityInterface $identity, ShipRequest $shipRequest): bool
    {
        return $this->isAdmin($identity) || $this->ownsClient($identity, $shipRequest->client_id);
    }

    /**
     * Check process permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\ShipRequest $shipRequest Ship request
     * @return bool
     */
    public function canProcess(?IdentityInterface $identity, ShipRequest $shipRequest): bool
    {
        return $this->isAdmin($identity) && $shipRequest->isSubmitted();
    }

    /**
     * Check edit permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\ShipRequest $shipRequest Ship request
     * @return bool
     */
    public function canEdit(?IdentityInterface $identity, ShipRequest $shipRequest): bool
    {
        return false;
    }

    /**
     * Check delete permission.
     *
     * @param \Authorization\IdentityInterface|null $identity Identity
     * @param \App\Model\Entity\ShipRequest $shipRequest Ship request
     * @return bool
     */
    public function canDelete(?IdentityInterface $identity, ShipRequest $shipRequest): bool
    {
        return false;
    }
}
