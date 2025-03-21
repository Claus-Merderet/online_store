<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartRepository $cartRepository,
    ) {
    }

    /**
     * @param array<object> $items
     */
    public function fillProductsInDTO(array &$items): void
    {
        foreach ($items as $item) {
            $product = $this->productRepository->find($item->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID:' . $item->productId);
            }
            $item->product = $product;
        }
    }

    public function checkExistCart(UserInterface $user): void
    {
        if ($this->cartRepository->findByUser($user) !== null) {
            throw new RuntimeException('Cart already exists');
        }
    }

    public function checkPermissions(bool $isCartCreator, bool $isAdmin): void
    {
        if (!$isCartCreator && !$isAdmin) {
            throw new AccessDeniedException('You do not have sufficient permissions to perform this action.');
        }
    }
}
