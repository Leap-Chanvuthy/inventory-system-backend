<?php
namespace App\Enums;

enum RawMaterialStatusEnum : string {
    case IN_STOCK = 'IN_STOCK';
    case OUT_OF_STOCK = 'OUT_OF_STOCK';
}