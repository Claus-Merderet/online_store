<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\ProductDTO;
use App\Entity\Product;

final readonly class ProductFactory
{
    public function create(ProductDTO $productDTO): Product
    {
        return new Product(
            $productDTO->id,
            $productDTO->name,
            $productDTO->measurements->weight,
            $productDTO->measurements->height,
            $productDTO->measurements->width,
            $productDTO->measurements->length,
            $productDTO->description,
            $productDTO->cost,
            $productDTO->tax,
            $productDTO->version,
        );
    }
}
