<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Enum\PackageStatus;
use Cake\ORM\Entity;

/**
 * Package Entity
 *
 * @property int $id
 * @property int $client_id
 * @property string $tracking_number
 * @property string $width
 * @property string $height
 * @property string $length
 * @property string $weight
 * @property \App\Model\Enum\DimensionUnit $dimension_unit
 * @property \App\Model\Enum\WeightUnit $weight_unit
 * @property string $contents_description
 * @property \App\Model\Enum\PackageStatus $status
 * @property \Cake\I18n\DateTime $received_at
 * @property \Cake\I18n\DateTime|null $shipped_at
 * @property \Cake\I18n\DateTime|null $ready_for_pickup_at
 * @property \Cake\I18n\DateTime|null $delivered_at
 * @property int|null $created_by_user_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Client $client
 * @property \App\Model\Entity\User|null $created_by_user
 * @property \App\Model\Entity\Invoice|null $invoice
 * @property \App\Model\Entity\PackageStatusHistory[] $package_status_histories
 * @property \App\Model\Entity\PackagesShipRequest[] $packages_ship_requests
 */
class Package extends Entity
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
        'tracking_number' => true,
        'width' => true,
        'height' => true,
        'length' => true,
        'weight' => true,
        'dimension_unit' => true,
        'weight_unit' => true,
        'contents_description' => true,
        'status' => true,
        'received_at' => true,
        'shipped_at' => true,
        'ready_for_pickup_at' => true,
        'delivered_at' => true,
        'created_by_user_id' => true,
        'created' => true,
        'modified' => true,
        'client' => true,
        'created_by_user' => true,
        'invoice' => true,
        'package_status_histories' => true,
        'packages_ship_requests' => true,
    ];

    /**
     * Whether the package accepts an invoice upload.
     *
     * @return bool
     */
    public function canUploadInvoice(): bool
    {
        return in_array($this->status, [PackageStatus::ReadyToSend, PackageStatus::NeedsReview], true);
    }

    /**
     * Whether the package has shipped.
     *
     * @return bool
     */
    public function isShipped(): bool
    {
        return $this->status === PackageStatus::Shipped;
    }

    /**
     * Whether the package is ready for pickup.
     *
     * @return bool
     */
    public function isReadyForPickup(): bool
    {
        return $this->status === PackageStatus::ReadyForPickup;
    }
}
