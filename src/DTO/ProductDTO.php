<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'The id field is required.')]
        #[Assert\Type(type: 'integer', message: 'The id field must be an integer.')]
        #[Assert\Positive(message: 'The id field must be a positive number.')]
        public ?int $id,
        public string $name,
        #[Assert\NotBlank(message: 'The measurements field is required.')]
        #[Assert\Valid]
        public MeasurementsDTO $measurements,
        public ?string $description,
        #[Assert\PositiveOrZero(message: 'The cost field must be zero or a positive number.')]
        public int $cost,
        #[Assert\PositiveOrZero(message: 'The tax field must be zero or a positive number.')]
        public int $tax,
        #[Assert\Positive(message: 'The version field must be a positive number.')]
        public int $version,
    ) {
    }
}
