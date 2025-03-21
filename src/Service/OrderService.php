<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

readonly class OrderService
{
    public function __construct(
        private ProductRepository $productRepository,
        private OrderRepository $orderRepository,
    ) {
    }

    public function validateDTO(OrderDTO $orderDTO): void
    {
        foreach ($orderDTO->orderProductsDTO as $orderProductDTO) {
            if ($orderProductDTO->product->getPrice() !== $orderProductDTO->price || $orderProductDTO->product->getTax() !== $orderProductDTO->tax) {
                throw new RuntimeException('The amount of items in the cart has changed. ID: ' . $orderProductDTO->productId);
            }
        }
    }

    /**
     * @param array<object> $items
     */
    public function fillOrderProductsInDTO(array &$items): void
    {
        foreach ($items as $item) {
            $product = $this->productRepository->find($item->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID:' . $item->productId);
            }
            $item->product = $product;
        }
    }

    public function checkPermissions(bool $isCartCreator, bool $isAdmin): void
    {
        if (!$isCartCreator && !$isAdmin) {
            throw new AccessDeniedException('You do not have sufficient permissions to perform this action.');
        }
    }

    public function findOrder(int $orderId): Order
    {
        $order = $this->orderRepository->find($orderId);
        if ($order === null) {
            throw new RuntimeException('Order not found. ID: ' . $orderId);
        }

        return $order;
    }
}
