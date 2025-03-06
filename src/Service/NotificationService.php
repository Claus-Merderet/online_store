<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RegisterUserDTO;
use App\Enum\NotificationType;

readonly class NotificationService
{
    public function __construct(private KafkaProducerService $kafkaProducerService)
    {
    }

    public function sendNotification(RegisterUserDTO $registerUserDTO): void
    {
        $notificationType = $registerUserDTO->email ? NotificationType::EMAIL : NotificationType::SMS;
        if ($notificationType === NotificationType::SMS) {
            $response['userPhone'] = $registerUserDTO->phone;
        } else {
            $response['userEmail'] = $registerUserDTO->email;
        }
        $response = [
            'promoId' => $registerUserDTO->promoId,
        ];

        $this->kafkaProducerService->sendMessage('topic_notification' . $notificationType->value, $response);
    }
}
