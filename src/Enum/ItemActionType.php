<?php

declare(strict_types=1);

namespace App\Enum;

enum ItemActionType: string
{
    case ADD = 'add';
    case REMOVE = 'remove';
}
