<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CartDto;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class CartService
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function fillProductsDTO(CartDTO $cartDTO): JsonResponse|null
    {
        foreach ($cartDTO->cartItems as $item) {
            $product = $this->productRepository->find($item->productId);
            if ($product === null) {
                return new JsonResponse(['error' => 'Product not found. ID:' . $item->productId], Response::HTTP_BAD_REQUEST);
            }
            $item->product = $product;
        }

        return null;
    }
}
