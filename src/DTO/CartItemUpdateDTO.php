<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\ItemActionType;
use Symfony\Component\Validator\Constraints as Assert;

class CartItemUpdateDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'productId is required.')]
        public int $productId,
        #[Assert\NotBlank(message: 'quantity is required.')]
        public int $quantity,
        #[Assert\NotBlank(message: 'action is required.')]
        public ItemActionType $action,
    ) {
    }
}
