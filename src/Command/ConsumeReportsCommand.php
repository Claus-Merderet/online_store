<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\KafkaConsumerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:consume-reports',
    description: 'Consume messages from Kafka and generate reports.',
)]
class ConsumeReportsCommand extends Command
{
    public function __construct(private readonly KafkaConsumerService $kafkaConsumer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->kafkaConsumer->consume('report_topic');

        return Command::SUCCESS;
    }
}
