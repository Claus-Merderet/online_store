<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDTO
{
    #[Assert\NotBlank(message: 'The userPhone field is required for type "sms".', groups: ['sms'])]
    #[Assert\Regex(pattern: '/^\+?[1-9]\d{1,14}$/', message: 'Invalid phone number.', groups: ['sms'])]
    public ?string $phone = null;

    #[Assert\NotBlank(message: 'The userEmail field is required for type "email".', groups: ['email'])]
    #[Assert\Email(message: 'Invalid email address.', groups: ['email'])]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'The password field is required.')]
    #[Assert\Regex(
        pattern: '/^(?=.*[!@#$%^&*(),.?":{}|<>]).{7,}$/',
        message: 'The password must be at least 7 characters long and contain at least one special character.'
    )]
    public string $password;

    #[Assert\Uuid(message: 'Invalid promoId. It must be a valid UUID.')]
    public ?string $promoId = null;

    public function __construct(?string $userPhone, ?string $userEmail, string $password, ?string $promoId)
    {
        $this->phone = $userPhone;
        $this->email = $userEmail;
        $this->password = $password;
        $this->promoId = $promoId;
    }
}