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
        foreach ($orderDTO->orderProductsDTO as $orderProductDTO) {
            if ($orderProductDTO->product->getPrice() !== $orderProductDTO->price || $orderProductDTO->product->getTax() !== $orderProductDTO->tax) {
                return new JsonResponse(
                    ['error' => 'The amount of items in the cart has changed. ID: ' . $orderProductDTO->productId],
                    Response::HTTP_CONFLICT,
                );
            }
        }

        return null;
    }

    public function fillOrderProducts(OrderDTO $orderDTO): JsonResponse|null
    {
        foreach ($orderDTO->orderProductsDTO as $orderProductDTO) {
            $product = $this->productRepository->find($orderProductDTO->productId);
            if ($product === null) {
                return new JsonResponse(
                    ['error' => 'Product not found. ID: ' . $orderProductDTO->productId],
                    Response::HTTP_BAD_REQUEST,
                );
            }
            $orderProductDTO->product = $product;
        }

        return null;
    }
}
