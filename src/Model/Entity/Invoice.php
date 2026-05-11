<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Invoice Entity
 *
 * @property int $id
 * @property int $package_id
 * @property int $uploaded_by_user_id
 * @property int|null $reviewed_by_user_id
 * @property string $file_path
 * @property string $original_filename
 * @property string $mime_type
 * @property int $file_size
 * @property \App\Model\Enum\InvoiceReviewStatus $review_status
 * @property string|null $admin_notes
 * @property \Cake\I18n\DateTime $uploaded_at
 * @property \Cake\I18n\DateTime|null $reviewed_at
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Package $package
 * @property \App\Model\Entity\User $uploaded_by_user
 * @property \App\Model\Entity\User|null $reviewed_by_user
 */
class Invoice extends Entity
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
        'uploaded_by_user_id' => true,
        'reviewed_by_user_id' => true,
        'file_path' => true,
        'original_filename' => true,
        'mime_type' => true,
        'file_size' => true,
        'review_status' => true,
        'admin_notes' => true,
        'uploaded_at' => true,
        'reviewed_at' => true,
        'created' => true,
        'modified' => true,
        'package' => true,
        'uploaded_by_user' => true,
        'reviewed_by_user' => true,
    ];
}
