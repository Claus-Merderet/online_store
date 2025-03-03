<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Enum\ReportType;
use App\Service\KafkaProducerService;
use App\Service\ReportGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KafkaProducerService $kafkaProducerService,
        private readonly ReportGeneratorService $reportGeneratorService,
    ) {
    }

    #[Route('/api/report/sales', name: 'create_sales_report', methods: ['GET'])]
    public function create(): JsonResponse
    {
        $report = new Report(ReportType::DAILY_SALES_REPORT);
        $this->entityManager->persist($report);
        $this->entityManager->flush();
        $this->kafkaProducerService->sendMessage('report_topic', ['report_id' => $report->getId()]);

        return new JsonResponse(['report_id' => $report->getId()], Response::HTTP_OK);
    }

    #[Route('/api/report/sales1', name: 'create_sales_report1', methods: ['GET'])]
    public function create1(): JsonResponse
    {
        $this->reportGeneratorService->generate('01955dd8-177a-74f0-8b0b-97fd5ddefdad');

        return new JsonResponse(['report_id' => '01955dd8-177a-74f0-8b0b-97fd5ddefdad'], Response::HTTP_OK);
    }
}
