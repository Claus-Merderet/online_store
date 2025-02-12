<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Product;

class OrderProductDTO
{
    public function __construct(// TODO: добавить валидацию
        public int $productId,
        public int $price,
        public int $amount,
        public int $tax,
        public ?Product $product,
    ) {
    }
}
