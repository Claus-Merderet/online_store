<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Report;
use App\Enum\ReportType;
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
        private readonly ReportGeneratorService $generatorService,
    ) {
    }

    #[Route('/api/report/sales', name: 'create_sales_report', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $report = new Report(ReportType::DAILY_SALES_REPORT);
        //        $this->entityManager->persist($report);
        //        $this->entityManager->flush();
        $this->generatorService->generateAsync($report);

        return new JsonResponse(['report_id' => $report->getId()], Response::HTTP_OK);
    }
}
