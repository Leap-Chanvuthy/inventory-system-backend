<?php
namespace App\Enums;

enum SupplierStatusEnum : string {
    case ACTIVE = 'SERVICE';
    case INACTIVE = 'PRODUCT';
    case SUSPENDED = 'SUSPENDED';
}