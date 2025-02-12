<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Entity\OrderProducts;
use App\Entity\OrderStatusHistory;
use App\Entity\User;
use App\Enum\StatusName;

class OrderFactory
{
    public function create(OrderDTO $orderDTO, User $user): Order
    {
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
