<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

// Represents a single exercise inside a workout day.
class ExerciseDTO
{
    #[Assert\NotBlank(message: 'Exercise name is required.')]
    #[Assert\Length(max: 150, maxMessage: 'Exercise name cannot exceed 150 characters.')]
    public readonly string $name;

    #[Assert\PositiveOrZero(message: 'Sets must be zero or a positive number.')]
    public readonly ?int $sets;

    #[Assert\PositiveOrZero(message: 'Reps must be zero or a positive number.')]
    public readonly ?int $reps;

    public readonly ?string $notes;

    public function __construct(string $name, ?int $sets, ?int $reps, ?string $notes)
    {
        $this->name  = $name;
        $this->sets  = $sets;
        $this->reps  = $reps;
        $this->notes = $notes;
    }
}
