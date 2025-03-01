<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Product;
use Symfony\Component\Validator\Constraints as Assert;

class OrderProductDTO
{
    public function __construct(
        #[Assert\Positive(message: 'The weight field must be a positive number.')]
        public int $productId,
        #[Assert\Positive(message: 'The weight field must be a positive number.')]
        public int $price,
        #[Assert\Positive(message: 'The weight field must be a positive number.')]
        public int $amount,
        #[Assert\Positive(message: 'The weight field must be a positive number.')]
        public int $tax,
        public ?Product $product,
    ) {
    }
}
