<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CartDTO
{
    /**
     *  @param CartItemDTO[] $cartItem
     */
    public function __construct(
        #[Assert\NotBlank(message: 'The CartItem field is required.')]
        #[Assert\Valid]
        public array $cartItem,
    ) {
    }
}
