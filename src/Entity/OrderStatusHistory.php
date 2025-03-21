<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\StatusName;
use App\Repository\OrderStatusHistoryRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderStatusHistoryRepository::class)]
#[ORM\Table(name: '`order_status_history`')]
class OrderStatusHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:index'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderStatusHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column(enumType: StatusName::class)]
    #[Groups(['order:index'])]
    private StatusName $statusName;

    #[ORM\Column(type: Types::DATETIMETZ_MUTABLE)]
    #[Groups(['order:index'])]
    private DateTimeInterface $createdAt;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:index'])]
    private ?string $comment;

    #[ORM\ManyToOne(inversedBy: 'orderStatusHistories')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:index'])]
    private User $createdBy;

    public function __construct(
        Order $order,
        StatusName $statusName,
        ?string $comment,
        User $createdBy,
    ) {
        $this->order = $order;
        $this->statusName = $statusName;
        $this->createdAt = new DateTime();
        $this->comment = $comment;
        $this->createdBy = $createdBy;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
