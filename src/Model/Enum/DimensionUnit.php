<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum DimensionUnit: string implements EnumLabelInterface
{
    case Inch = 'in';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::Inch => __('Inches'),
        };
    }
}
