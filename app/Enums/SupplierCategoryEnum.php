<?php

namespace App\Enums;

enum SupplierCategoryEnum: string
{
    case PRODUCT = 'PRODUCT';
    case SERVICE = 'SERVICE';

    public static function options(): array
    {
        return [
            self::PRODUCT->value => 'Product',
            self::SERVICE->value => 'Service',
        ];
    }
}
