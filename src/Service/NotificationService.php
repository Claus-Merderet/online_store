<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\NotificationType;

class NotificationService
{
    public function sendNotification(NotificationType $type, string $recipient, ?string $promoId): void
    {
        $response = [
            'type' => $type->value,
            'promoId' => $promoId,
        ];
        if ($type === NotificationType::SMS) {
            $response['userPhone'] = $recipient;
            $this->sendSms($response);
        } elseif ($type === NotificationType::EMAIL) {
            $response['userEmail'] = $recipient;
            $this->sendEmail($response);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendSms(array $data): void
    {
        // Логика отправки SMS
    }

    /**
     * @param array<string, mixed> $data
     */
    private function sendEmail(array $data): void
    {
        // Логика отправки email
    }
}
