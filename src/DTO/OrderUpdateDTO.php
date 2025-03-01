<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class OrderUpdateDTO
{
    /**
     * @param OrderItemUpdateDTO[] $updateOrderItems
     */
    public function __construct(
        #[Assert\NotBlank(message: 'updateOrderItems is required.')]
        #[Assert\Valid]
        public array $updateOrderItems,
    ) {
    }
}
