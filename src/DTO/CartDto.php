<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CartDto
{
    /**
     *  @param CartItemDTO[] $cartItems
     */
    public function __construct(
        #[Assert\NotBlank(message: 'The CartItems field is required.')]
        #[Assert\Valid]
        public array $cartItems,
    ) {
    }
}
