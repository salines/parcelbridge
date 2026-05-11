<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum PackageStatus: string implements EnumLabelInterface
{
    case ReadyToSend = 'ready_to_send';
    case PendingInvoiceReview = 'pending_invoice_review';
    case InvoiceApproved = 'invoice_approved';
    case NeedsReview = 'needs_review';
    case ShipRequested = 'ship_requested';
    case Shipped = 'shipped';
    case ReadyForPickup = 'ready_for_pickup';
    case Delivered = 'delivered';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::ReadyToSend => __('Ready to Send'),
            self::PendingInvoiceReview => __('Pending Invoice Review'),
            self::InvoiceApproved => __('Invoice Approved'),
            self::NeedsReview => __('Needs Review'),
            self::ShipRequested => __('Ship Requested'),
            self::Shipped => __('Shipped'),
            self::ReadyForPickup => __('Ready for Pickup'),
            self::Delivered => __('Delivered'),
        };
    }
}
