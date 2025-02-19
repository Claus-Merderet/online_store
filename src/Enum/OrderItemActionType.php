<?php

declare(strict_types=1);

namespace App\Enum;

enum OrderItemActionType: string
{
    case ADD = 'add';
    case REMOVE = 'remove';
}
