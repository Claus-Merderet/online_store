<?php declare(strict_types=1);

namespace App\DTO;
use Symfony\Component\Validator\Constraints as Assert;

class MeasurementsDTO
{
    public function __construct(
        #[Assert\NotBlank(message: "The weight field is required.")]
        #[Assert\Type(type: 'integer', message: "The weight field must be an integer.")]
        #[Assert\Positive(message: "The weight field must be a positive number.")]
        public int $weight,

        #[Assert\NotBlank(message: "The height field is required.")]
        #[Assert\Type(type: 'integer', message: "The height field must be an integer.")]
        #[Assert\Positive(message: "The height field must be a positive number.")]
        public int $height,

        #[Assert\NotBlank(message: "The width field is required.")]
        #[Assert\Type(type: 'integer', message: "The width field must be an integer.")]
        #[Assert\Positive(message: "The width field must be a positive number.")]
        public int $width,

        #[Assert\NotBlank(message: "The length field is required.")]
        #[Assert\Type(type: 'integer', message: "The length field must be an integer.")]
        #[Assert\Positive(message: "The length field must be a positive number.")]
        public int $length
    ) {}
}