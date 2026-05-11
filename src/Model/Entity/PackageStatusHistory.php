<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * PackageStatusHistory Entity
 *
 * @property int $id
 * @property int $package_id
 * @property int|null $changed_by_user_id
 * @property \App\Model\Enum\UserRole|null $changed_by_role
 * @property \App\Model\Enum\PackageStatus|null $old_status
 * @property \App\Model\Enum\PackageStatus $new_status
 * @property string|null $note
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Package $package
 * @property \App\Model\Entity\User|null $changed_by_user
 */
class PackageStatusHistory extends Entity
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
        'changed_by_user_id' => true,
        'changed_by_role' => true,
        'old_status' => true,
        'new_status' => true,
        'note' => true,
        'created' => true,
        'package' => true,
        'changed_by_user' => true,
    ];
}
