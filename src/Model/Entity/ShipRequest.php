<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Enum\ShipRequestStatus;
use Cake\ORM\Entity;

/**
 * ShipRequest Entity
 *
 * @property int $id
 * @property int $client_id
 * @property int $submitted_by_user_id
 * @property int|null $processed_by_user_id
 * @property \App\Model\Enum\ShipRequestStatus $status
 * @property string|null $processing_reference
 * @property \Cake\I18n\DateTime $submitted_at
 * @property \Cake\I18n\DateTime|null $processed_at
 * @property string|null $notes
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\User $submitted_by_user
 * @property \App\Model\Entity\User|null $processed_by_user
 * @property \App\Model\Entity\PackagesShipRequest[] $packages_ship_requests
 */
class ShipRequest extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'client_id' => true,
        'submitted_by_user_id' => true,
        'processed_by_user_id' => true,
        'status' => true,
        'processing_reference' => true,
        'submitted_at' => true,
        'processed_at' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'client' => true,
        'submitted_by_user' => true,
        'processed_by_user' => true,
        'packages_ship_requests' => true,
    ];

    /**
     * Whether the ship request is submitted.
     *
     * @return bool
     */
    public function isSubmitted(): bool
    {
        return $this->status === ShipRequestStatus::Submitted;
    }
}
