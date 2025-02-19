<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateOrderDTO
{
    /**
     * @param UpdateOrderItemDTO[] $updateOrderItems
     */
    public function __construct(
        #[Assert\NotBlank(message: 'updateOrderItems is required.')]
        public array $updateOrderItems,
    ) {
    }
}
