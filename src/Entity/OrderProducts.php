<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderProductsRepository::class)]
#[ORM\Table(name: '`order_products`')]
class OrderProducts //TODO убрать мн.ч
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderProducts')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column(length: 255)]
    #[Groups(['order:index'])]
    private string $productName;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $price;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $amount;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $height;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $weight;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $length;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $width;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $tax;

    #[ORM\Column]
    #[Groups(['order:index'])]
    private int $version;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:index'])]
    private ?string $description = null;

    public function __construct(
        Order $order,
        Product $product,
        int $amount,
    ) {
        $this->order = $order;
        $this->product = $product;
        $this->productName = $product->getName();
        $this->price = $product->getPrice();
        $this->amount = $amount;
        $this->height = $product->getHeight();
        $this->weight = $product->getWeight();
        $this->length = $product->getlength();
        $this->width = $product->getWidth();
        $this->tax = $product->getTax();
        $this->version = $product->getVersion();
        $this->description = $product->getDescription();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }
}
