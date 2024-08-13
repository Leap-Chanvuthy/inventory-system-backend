<?php
namespace App\Enums;

enum UserRoleEnum : string {
    case ADMIN = 'ADMIN';
    case STOCK_CONTROLLER = 'STOCK_CONTROLLER';
    case SALER = 'SALLER';
    case USER = 'USER';
}