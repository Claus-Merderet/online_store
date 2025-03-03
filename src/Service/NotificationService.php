<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\NotificationType;

class NotificationService
{
    public function __construct(private readonly KafkaProducerService $kafkaProducerService)
    {
    }

    public function sendNotification(NotificationType $type, string $recipient, ?string $promoId): void
    {
        $response = [
            'promoId' => $promoId,
        ];
        if ($type === NotificationType::SMS) {
            $response['userPhone'] = $recipient;
        } elseif ($type === NotificationType::EMAIL) {
            $response['userEmail'] = $recipient;
        }
        $this->kafkaProducerService->sendMessage('topic_notification' . $type->value, $response);
    }
}
