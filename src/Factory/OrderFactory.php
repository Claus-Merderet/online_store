<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Entity\OrderStatusHistory;
use App\Entity\User;
use App\Enum\StatusName;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

final readonly class OrderFactory
{
    public function create(OrderDTO $orderDTO, UserInterface $user): Order
    {
        /* @var User $user */
        Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));
        $order = new Order(
            $orderDTO->notificationType,
            $user,
            $orderDTO->address,
            $orderDTO->kladrId,
            $orderDTO->userPhone,
            $orderDTO->deliveryType,
        );
        $statusHistory = new OrderStatusHistory($order, StatusName::REQUIRES_PAYMENT, '', $user);
        $order->addStatusHistory($statusHistory);
        foreach ($orderDTO->orderProductsDTO as $productDTO) {
            $orderProduct = new OrderProducts($order, $productDTO->product, $productDTO->amount);
            $order->addProduct($orderProduct);
        }

        return $order;
    }
}
