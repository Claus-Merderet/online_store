<?php

declare(strict_types=1);

namespace App\Factory;

use App\DTO\OrderDTO;
use App\Entity\Order;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Webmozart\Assert\Assert;

final readonly class OrderFactory
{
    public function create(OrderDTO $orderDTO, UserInterface $user): Order
    {
        /* @var User $user */
        Assert::isInstanceOf($user, User::class, sprintf('Invalid user type %s', get_class($user)));

        $order = Order::create(
            $orderDTO->notificationType,
            $user,
            $orderDTO->address,
            $orderDTO->kladrId,
            $orderDTO->userPhone,
            $orderDTO->deliveryType,
        );

        foreach ($orderDTO->orderProductsDTO as $productDTO) {
            $order->addProduct($productDTO->product, $productDTO->amount);
        }

        return $order;
    }
}
