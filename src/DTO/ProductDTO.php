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

        #[Assert\NotBlank(message: "The name field is required.")]
        #[Assert\Type(type: 'string', message: "The name field must be a string.")]
        public string $name,

        #[Assert\NotBlank(message: "The measurements field is required.")]
        #[Assert\Valid]
        public MeasurementsDTO $measurements,

        public ?string $description,

        #[Assert\NotBlank(message: "The cost field is required.")]
        #[Assert\Type(type: 'integer', message: "The cost field must be an integer.")]
        #[Assert\PositiveOrZero(message: "The cost field must be zero or a positive number.")]
        public int $cost,

        #[Assert\NotBlank(message: "The tax field is required.")]
        #[Assert\Type(type: 'integer', message: "The tax field must be an integer.")]
        #[Assert\PositiveOrZero(message: "The tax field must be zero or a positive number.")]
        public int $tax,

        #[Assert\NotBlank(message: "The version field is required.")]
        #[Assert\Type(type: 'integer', message: "The version field must be an integer.")]
        #[Assert\Positive(message: "The version field must be a positive number.")]
        public int $version
    ) {}
}
