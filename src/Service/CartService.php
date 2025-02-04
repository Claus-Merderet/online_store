<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CartDto;
use App\Entity\User;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class CartService
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    public function validateDTO(CartDTO $cartDTO): JsonResponse|null
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

    public function checkPermission(User $cartUser, UserInterface $authUser, bool $isAdmin): JsonResponse|null
    {
        if ($cartUser !== $authUser && $isAdmin === false) {
            return new JsonResponse(
                ['error' => 'You do not have sufficient permissions to perform this action.'],
                Response::HTTP_FORBIDDEN,
            );
        }

        return null;
    }
}
