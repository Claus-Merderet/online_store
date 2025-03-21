<?php

declare(strict_types=1);

namespace App\Entity;

use App\DTO\ProductDTO;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\Table(name: '`products`')]
#[Groups(['cart'])]
class Product
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        private int $id,
        #[ORM\Column(length: 255)]
        private string $name,
        #[ORM\Column]
        private int $weight,
        #[ORM\Column]
        private int $height,
        #[ORM\Column]
        private int $width,
        #[ORM\Column]
        private int $length,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $description,
        #[ORM\Column]
        private int $price,
        #[ORM\Column]
        private int $tax,
        #[ORM\Column]
        private int $version,
    ) {
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getTax(): int
    {
        return $this->tax;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public static function createFromDTO(ProductDTO $productDTO): self
    {
        return new self(
            $productDTO->id,
            $productDTO->name,
            $productDTO->measurements->weight,
            $productDTO->measurements->height,
            $productDTO->measurements->width,
            $productDTO->measurements->length,
            $productDTO->description,
            $productDTO->cost,
            $productDTO->tax,
            $productDTO->version,
        );
    }
}
