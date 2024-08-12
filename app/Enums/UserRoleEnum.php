<?php
namespace App\Enums;

enum UserRoleEnum : string {
    case ADMIN = 'admin';
    case STOCK_CONTROLLER = 'stock_controller';
    case SALER = 'saler';
    case USER = 'user';
}