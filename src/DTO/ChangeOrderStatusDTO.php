<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\StatusName;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeOrderStatusDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The CartItems field is required.')]
        #[Assert\Type(type: 'integer', message: 'orderId must be an integer.')]
        public int $orderId,
        #[Assert\NotBlank(message: 'The CartItems field is required.')]
        #[Assert\Type(type: StatusName::class, message: 'statusName must be a StatusName.')]
        public StatusName $statusName,
    ) {
    }
}
