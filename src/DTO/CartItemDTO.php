<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class CartItemDTO
{
    public function __construct(
        #[Assert\Positive(message: 'The productId field must be a positive number.')]
        public int $productId,
        #[Assert\Positive(message: 'The quantity field must be a positive number.')]
        public int $quantity,
        public ?Product $product,
    ) {
    }
}
