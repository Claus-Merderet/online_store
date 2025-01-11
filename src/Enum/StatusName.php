<?php declare(strict_types=1);

namespace App\Enum;

enum StatusName: string
{
    case REQUIRES_PAYMENT = 'requires_payment';
    case SUCCESS_PAYMENT = 'success_payment';
    case COMPLETED = 'completed';
}
