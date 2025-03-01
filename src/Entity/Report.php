<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ReportStatus;
use App\Enum\ReportType;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private UuidInterface $id;

    #[ORM\Column(length: 20)]
    private ReportStatus $status = ReportStatus::PENDING;

    #[ORM\Column(nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column]
    private ReportType $reportType;

    #[ORM\Column]
    private DateTimeInterface $createdAt;

    public function __construct(ReportType $reportType)
    {
        $this->id = Uuid::uuid4();
        $this->reportType = $reportType;
        $this->createdAt = new DateTime();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getStatus(): ReportStatus
    {
        return $this->status;
    }

    public function setStatus(ReportStatus $status): void
    {
        $this->status = $status;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getReportType(): ReportType
    {
        return $this->reportType;
    }

    public function setReportType(ReportType $reportType): void
    {
        $this->reportType = $reportType;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
}
