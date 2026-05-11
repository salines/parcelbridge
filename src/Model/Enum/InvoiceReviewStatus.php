<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum InvoiceReviewStatus: string implements EnumLabelInterface
{
    case Pending = 'pending';
    case Approved = 'approved';
    case NeedsReview = 'needs_review';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::NeedsReview => __('Needs Review'),
        };
    }
}
