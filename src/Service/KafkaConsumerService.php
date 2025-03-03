<?php

declare(strict_types=1);

namespace App\Service;

use Interop\Queue\Context;

readonly class KafkaConsumerService
{
    public function __construct(private ReportGeneratorService $reportService, private Context $context)
    {
    }

    public function consume(string $topic): void
    {
        $queue = $this->context->createQueue($topic);
        $consumer = $this->context->createConsumer($queue);

        while (true) {
            $message = $consumer->receive();
            if ($message) {
                $data = json_decode($message->getBody(), true);
                $this->reportService->generate($data['report_id']);
                $consumer->acknowledge($message);
            }
        }
    }
}
