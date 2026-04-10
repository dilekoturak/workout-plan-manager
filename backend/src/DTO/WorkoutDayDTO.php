<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

// Represents a single workout day inside a plan, with its exercises.
class WorkoutDayDTO
{
    #[Assert\NotBlank(message: 'Day name is required.')]
    #[Assert\Length(max: 100, maxMessage: 'Day name cannot exceed 100 characters.')]
    public readonly string $name;

    /** @var ExerciseDTO[] */
    #[Assert\Valid]
    #[Assert\All([
        new Assert\Type(ExerciseDTO::class),
    ])]
    public readonly array $exercises;

    /**
     * @param ExerciseDTO[] $exercises
     */
    public function __construct(string $name, array $exercises = [])
    {
        $this->name      = $name;
        $this->exercises = $exercises;
    }
}
