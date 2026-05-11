<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PackagesShipRequest Entity
 *
 * @property int $id
 * @property int $package_id
 * @property int $ship_request_id
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Package $package
 * @property \App\Model\Entity\ShipRequest $ship_request
 */
class PackagesShipRequest extends Entity
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
        'package_id' => true,
        'ship_request_id' => true,
        'created' => true,
        'package' => true,
        'ship_request' => true,
    ];
}
