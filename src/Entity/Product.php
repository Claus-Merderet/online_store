<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\ProductDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`products`')]
class Product
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private int $weight;

    #[ORM\Column]
    private int $height;

    #[ORM\Column]
    private int $width;

    #[ORM\Column]
    private int $length;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description;

    #[ORM\Column]
    private int $price;

    #[ORM\Column]
    private int $tax;

    #[ORM\Column]
    private int $version;

    public function __construct(ProductDTO $dto)
    {
        $this->id = $dto->id;
        $this->name = $dto->name;
        $this->weight = $dto->measurements->weight;
        $this->height = $dto->measurements->height;
        $this->width = $dto->measurements->width;
        $this->length = $dto->measurements->length;
        $this->description = $dto->description;
        $this->price = $dto->cost;
        $this->tax = $dto->tax;
        $this->version = $dto->version;
    }

    public function syncWithDTO(ProductDTO $dto): void
    {
        $this->id = $dto->id;
        $this->name = $dto->name;
        $this->weight = $dto->measurements->weight;
        $this->height = $dto->measurements->height;
        $this->width = $dto->measurements->width;
        $this->length = $dto->measurements->length;
        $this->description = $dto->description;
        $this->price = $dto->cost;
        $this->tax = $dto->tax;
        $this->version = $dto->version;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function setLength(int $length): static
    {
        $this->length = $length;

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

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function setTax(int $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): static
    {
        $this->version = $version;

        return $this;
    }
}
