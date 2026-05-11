<?php
declare(strict_types=1);

namespace App\Model\Enum;

use Cake\Database\Type\EnumLabelInterface;

enum UserRole: string implements EnumLabelInterface
{
    case Admin = 'admin';
    case Client = 'client';

    /**
     * @inheritDoc
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => __('Admin'),
            self::Client => __('Client'),
        };
    }
}
