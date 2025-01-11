<?php declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatusName;
use App\Repository\OrderStatusHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderStatusHistoryRepository::class)]
#[ORM\Table(name: '`order_status_history`')]
class OrderStatusHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderStatusHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(enumType: StatusName::class)]
    private StatusName $statusName;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'orderStatusHistories')]
    private ?User $changedBy = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getStatusName(): ?StatusName
    {
        return $this->statusName;
    }

    public function setStatusName(StatusName $statusName): static
    {
        $this->statusName = $statusName;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function setChangedBy(?User $changedBy): static
    {
        $this->changedBy = $changedBy;

        return $this;
    }
}
