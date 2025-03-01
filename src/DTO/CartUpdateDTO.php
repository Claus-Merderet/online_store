<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class CartUpdateDTO
{
    /**
     * @param CartItemUpdateDTO[] $cartItemUpdateDTO
     */
    public function __construct(
        #[Assert\NotBlank(message: 'The cartItemUpdateDTO field is required.')]
        #[Assert\Valid]
        public array $cartItemUpdateDTO,
    ) {
    }
}
