<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MeasurementsDTO
{
    public function __construct(
        #[Assert\Positive(message: 'The weight field must be a positive number.')]
        public int $weight,
        #[Assert\Positive(message: 'The height field must be a positive number.')]
        public int $height,
        #[Assert\Positive(message: 'The width field must be a positive number.')]
        public int $width,
        #[Assert\Positive(message: 'The length field must be a positive number.')]
        public int $length,
    ) {
    }
}
