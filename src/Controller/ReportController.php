<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Enum\ReportType;
use App\Service\KafkaProducerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly KafkaProducerService $kafkaProducerService,
    ) {
    }

    #[Route('/api/report/sales', name: 'create_sales_report', methods: ['GET'])]
    #[OA\Get(summary: 'Generate report sales')]
    public function create(): JsonResponse
    {
        try {
            $report = new Report(ReportType::DAILY_SALES_REPORT);
            $this->entityManager->persist($report);
            $this->entityManager->flush();
            $this->kafkaProducerService->sendMessage('report_topic', ['report_id' => $report->getId()]);

            return new JsonResponse(['report_id' => $report->getId()], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse(
                ['error' => 'Failed to create a report: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
