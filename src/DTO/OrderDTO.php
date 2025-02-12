<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\DeliveryType;
use App\Enum\NotificationType;

readonly class OrderDTO
{
    /**
     * @param OrderProductDTO[] $orderProductsDTO
     */
    public function __construct(// TODO: добавить валидацию
        public NotificationType $notificationType,
        public array $orderProductsDTO,
        public ?string $address,
        public ?int $kladrId,
        public ?string $userPhone,
        public ?string $userEmail,
        public DeliveryType $deliveryType,
    ) {
    }
}
