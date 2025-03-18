<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\OrderChangeStatusDTO;
use App\DTO\OrderDTO;
use App\DTO\OrderUpdateDTO;
use App\Entity\Cart;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Entity\OrderStatusHistory;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\ItemActionType;
use App\Factory\OrderFactory;
use App\Repository\OrderProductsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

readonly class OrderService
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private OrderFactory $orderFactory,
        private OrderRepository $orderRepository,
        private OrderProductsRepository $orderProductsRepository,
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

    public function fillOrderProducts(OrderDTO $orderDTO): void
    {
        foreach ($orderDTO->orderProductsDTO as $orderProductDTO) {
            $product = $this->productRepository->find($orderProductDTO->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID: ' . $orderProductDTO->productId);
            }
            $orderProductDTO->product = $product;
        }
    }

    public function createOrder(UserInterface $user, OrderDTO $orderDTO): Order
    {
        $order = $this->orderFactory->create($orderDTO, $user);
        $this->entityManager->persist($order);
        $cart = $this->entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        if ($cart instanceof Cart) {
            $this->entityManager->remove($cart);
        }
        $this->entityManager->flush();

        return $order;
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

    /**
     * @throws Exception
     */
    public function updateOrder(Order $order, OrderUpdateDTO $orderUpdateDTO): Order
    {
        foreach ($orderUpdateDTO->updateOrderItems as $orderItemDTO) {
            $product = $this->productRepository->find($orderItemDTO->productId);
            if ($product === null) {
                throw new RuntimeException('Product not found. ID: ' . $orderItemDTO->productId);
            }

            $orderProduct = $this->orderProductsRepository->findOneBy(['order' => $order, 'product' => $product,]);
            if ($orderItemDTO->action === ItemActionType::ADD) {
                $this->addOrUpdateOrderProducts($orderProduct, $order, $product, $orderItemDTO->quantity);
            } elseif ($orderItemDTO->action === ItemActionType::REMOVE) {
                if ($orderProduct instanceof OrderProducts) {
                    $this->removeOrUpdateOrderProducts($orderProduct, $order, $orderItemDTO->quantity);
                } else {
                    throw new RuntimeException('The product to be deleted was not found in the order. ID: ' . $orderItemDTO->productId);
                }
            }
        }

        $this->entityManager->flush();

        return $order;
    }

    private function addOrUpdateOrderProducts(?OrderProducts $orderProduct, Order $order, Product $product, int $quantity): void
    {
        if ($orderProduct instanceof OrderProducts) {
            $orderProduct->setAmount($orderProduct->getAmount() + $quantity);
        } else {
            $orderProduct = new OrderProducts($order, $product, $quantity);
        }
        $this->entityManager->persist($orderProduct);
    }

    private function removeOrUpdateOrderProducts(OrderProducts $orderProduct, Order $order, int $quantity): void
    {
        $newAmount = $orderProduct->getAmount() - $quantity;
        if ($newAmount < 1) {
            $this->entityManager->remove($orderProduct);
            $order->removeProduct($orderProduct);
        } else {
            $orderProduct->setAmount($newAmount);
            $this->entityManager->persist($orderProduct);
        }
    }
}
