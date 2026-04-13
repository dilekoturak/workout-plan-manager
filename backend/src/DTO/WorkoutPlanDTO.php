<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class WorkoutPlanDTO
{
    #[Assert\NotBlank(message: 'Plan name is required.')]
    #[Assert\Length(max: 150, maxMessage: 'Plan name cannot exceed 150 characters.')]
    public readonly string $name;

    /** @var WorkoutDayDTO[] */
    #[Assert\Valid]
    #[Assert\All([
        new Assert\Type(WorkoutDayDTO::class),
    ])]
    public readonly array $days;

    /**
     * @param WorkoutDayDTO[] $days
     */
    public function __construct(string $name, array $days = [])
    {
        $this->name = $name;
        $this->days = $days;
    }
}
