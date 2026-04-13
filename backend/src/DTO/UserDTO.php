<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserDTO
{
    #[Assert\NotBlank(message: 'First name is required.')]
    #[Assert\Length(max: 100, maxMessage: 'First name cannot exceed 100 characters.')]
    public readonly string $firstName;

    #[Assert\NotBlank(message: 'Last name is required.')]
    #[Assert\Length(max: 100, maxMessage: 'Last name cannot exceed 100 characters.')]
    public readonly string $lastName;

    #[Assert\NotBlank(message: 'Email is required.')]
    #[Assert\Email(message: 'Please provide a valid email address.')]
    public readonly string $email;

    public function __construct(string $firstName, string $lastName, string $email)
    {
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
        $this->email     = $email;
    }
}
