<?php

declare(strict_types=1);

namespace App\Service;

use Interop\Queue\Context;
use Interop\Queue\Exception;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;

readonly class KafkaProducerService
{
    public function __construct(private Context $context)
    {
    }

    /**
     * @throws InvalidDestinationException
     * @throws InvalidMessageException
     * @throws Exception
     */
    public function sendMessage(string $topic, array $message): void
    {
        $queue = $this->context->createQueue($topic);
        $message = $this->context->createMessage(json_encode($message));
        $this->context->createProducer()->send($queue, $message);
    }
}
