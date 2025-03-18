<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\StatusName;
use Symfony\Component\Validator\Constraints as Assert;

class OrderChangeStatusDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The orderId field is required.')]
        public int $orderId,
        #[Assert\NotBlank(message: 'The statusName field is required.')]
        public StatusName $statusName,
        public string $comment = '',
    ) {
    }
}
