<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CartItemDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The productId field is required.')]
        #[Assert\Type(type: 'integer', message: 'The productId field must be an integer.')]
        #[Assert\Positive(message: 'The productId field must be a positive number.')]
        public int $productId,
        #[Assert\NotBlank(message: 'The quantity field is required.')]
        #[Assert\Type(type: 'integer', message: 'The quantity field must be an integer.')]
        #[Assert\Positive(message: 'The quantity field must be a positive number.')]
        public int $quantity,
    ) {
    }
}
