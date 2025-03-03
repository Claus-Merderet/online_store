<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report;
use App\Enum\ReportStatus;
use App\Enum\StatusName;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class ReportGeneratorService
{
    public const REPORT_DIRECTORY = '/reports';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function generate(string $reportID): void
    {
        try {
            $report = $this->entityManager->getRepository(Report::class)->find($reportID);
            if ($report === null) {
                throw new RuntimeException('Report not found: ' . $reportID);
            }
            $soldProducts = $this->findSoldProductsToday();
            $time = new DateTime();
            $reportFilePath = $_ENV['REPORTS_DIR'] . self::REPORT_DIRECTORY . '/' . $time->format('Y-m-d_H-i-s') . '_' . $report->getId() . '.jsonl';
            if (empty($soldProducts)) {
                $this->filesystem->appendToFile($reportFilePath, 'No sales');
            } else {
                $grouped = [];
                foreach ($soldProducts as $item) {
                    $key = $item['product_id'] . '_' . $item['user_id'] . '_' . $item['price'];
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = $item;
                    } else {
                        $grouped[$key]['amount'] += $item['amount'];
                    }
                }
                $result = array_values($grouped);
                usort($result, function ($a, $b) {
                    return $a['product_id'] <=> $b['product_id'] ?: $a['user_id'] <=> $b['user_id'];
                });
            }

            $report->setFilePath($reportFilePath);
            $report->setCreatedAt($time);
            $report->setStatus(ReportStatus::CREATED);
            $this->entityManager->flush();
        } catch (Exception $e) {
            error_log('Error generating report: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     * @return array<array{
     *     product_name: string,
     *     price: int,
     *     amount: int,
     *     product_id: int,
     *     user_id: int
     * }> Возвращает массив проданных товаров за сегодня. Может быть пустым.
     */
    private function findSoldProductsToday(): array
    {
        $sql = <<<SQL
                SELECT
                op.product_name,
                op.price,
                op.amount,
                op.product_id,
                o.user_id
            FROM order_status_history osh
            JOIN orders o
                ON o.id = osh.order_id
            JOIN order_products op
                ON o.id = op.order_id
            WHERE status_name = :status
                AND created_at >= :startOfDay
        SQL;
        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery([
            'status' => StatusName::REQUIRES_PAYMENT->value,
            'startOfDay' => (new DateTime('today'))->format('Y-m-d H:i:s'),
        ]);

        return $result->fetchAllAssociative();
    }
}
