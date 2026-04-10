<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

// This DTO (Data Transfer Object) carries the data coming from the HTTP request body.
// It acts as a firewall between the raw HTTP input and the domain layer.
// Think of it like a .NET request model or input DTO with FluentValidation attributes.
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
