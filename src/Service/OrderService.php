<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\OrderDTO;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

readonly class OrderService
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function validateDTO(OrderDTO $orderDTO): JsonResponse|null
    {
        foreach ($orderDTO->orderProductsDTO as $orderProductsDTO) {
            $product = $this->productRepository->find($orderProductsDTO->productId);
            if ($product === null) {
                return new JsonResponse(
                    ['error' => 'Product not found. ID: ' . $orderProductsDTO->productId],
                    Response::HTTP_BAD_REQUEST,
                );
            } elseif ($product->getPrice() !== $orderProductsDTO->price || $product->getTax() !== $orderProductsDTO->tax) {
                return new JsonResponse(
                    ['error' => 'The amount of items in the cart has changed. ID: ' . $orderProductsDTO->productId],
                    Response::HTTP_CONFLICT,
                );
            }
            $orderProductsDTO->product = $product;
        }

        return null;
    }
}
