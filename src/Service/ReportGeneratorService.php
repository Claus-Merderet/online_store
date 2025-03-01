<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Report;
use App\Enum\StatusName;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Symfony\Component\Filesystem\Filesystem;

class ReportGeneratorService
{
    public const REPORT_DIRECTORY = '../reports';

    public function __construct(private readonly Filesystem $filesystem, private readonly Connection $connection)
    {
    }

    public function generateAsync(Report $report): void
    {
        //тут мессенджер
        $this->generate($report);
    }


    private function generate(Report $report): void
    {
        try {
            $soldProducts = $this->findSoldProductsToday();

            $grouped = array_reduce($soldProducts, function ($carry, $item) {
                $key = $item['product_id'] . '_' . $item['user_id'] . '_' . $item['price'];
                if (isset($carry[$key])) {
                    $carry[$key]['amount'] += $item['amount'];
                } else {
                    $carry[$key] = $item;
                }

                return $carry;
            }, []);
            $result = array_values($grouped);
            usort($result, function ($a, $b) {
                if ($a['product_id'] === $b['product_id']) {
                    return $a['user_id'] <=> $b['user_id'];
                }

                return $a['product_id'] <=> $b['product_id'];
            });
            $time = new DateTime();
            $reportFilePath = self::REPORT_DIRECTORY . '/' . $time->format('d-m-Y-H-i-s') . '_' . $report->getId() . '.jsonl';
            foreach ($soldProducts as $product) {
                $line = json_encode([
                    'product_name' => $product['product_name'],
                    'price' => $product['price'],
                    'amount' => $product['amount'],
                    'product_id' => $product['product_id'],
                    'user' => [
                        'id' => $product['user_id'],
                    ],
                ]);

                $this->filesystem->appendToFile($reportFilePath, $line . PHP_EOL);
            }
            $report->setFilePath($reportFilePath);
            $report->setCreatedAt($time);

        } catch (Exception $e) {
            // Тут должно быть логирование ошибки
        }
        // Отправляем событие в Kafka
    }

    /**
     * @throws Exception
     * @return array{
     *          array{
     *              product_name: string,
     *              price: int,
     *              amount: int,
     *              product_id: int,
     *              user_id: int
     *          }
     *     }
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
