<?php

declare(strict_types=1);

namespace App\Enum;

enum ReportStatus: string
{
    case PENDING = 'Pending';
    case CREATED = 'Created';
}
