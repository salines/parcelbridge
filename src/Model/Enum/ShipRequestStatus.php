<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum ShipRequestStatus: string implements EnumLabelInterface
{
    case Submitted = 'submitted';
    case Processed = 'processed';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::Submitted => __('Submitted'),
            self::Processed => __('Processed'),
        };
    }
}
