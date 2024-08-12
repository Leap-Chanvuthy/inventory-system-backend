<?php

namespace App\Enums;

enum ProductCategoryEnum: string
{
    case RAW_MATERIALS = 'raw_materials';
    case PACKAGING = 'packaging';
    case WORK_IN_PROCESS = 'work_in_process';
    case FINISHED_GOODS = 'finished_goods';
}
