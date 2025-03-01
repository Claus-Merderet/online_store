<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enum\DeliveryType;
use App\Enum\NotificationType;
use Symfony\Component\Validator\Constraints as Assert;

readonly class OrderDTO
{
    /**
     * @param OrderProductDTO[] $orderProductsDTO
     */
    public function __construct(
        public NotificationType $notificationType,
        #[Assert\NotBlank(message: 'productId is required.')]
        #[Assert\Valid]
        public array $orderProductsDTO,
        public ?string $address,
        public ?int $kladrId,
        public ?string $userPhone,
        public ?string $userEmail,
        public DeliveryType $deliveryType,
    ) {
    }
}
