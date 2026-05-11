<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum WeightUnit: string implements EnumLabelInterface
{
    case Pound = 'lb';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::Pound => __('Pounds'),
        };
    }
}
