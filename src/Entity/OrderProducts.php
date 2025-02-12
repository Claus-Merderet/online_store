<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: OrderProductsRepository::class)]
#[ORM\Table(name: '`order_products`')]
class OrderProducts
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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
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

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTax(int $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
