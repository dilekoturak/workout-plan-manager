<?php

namespace App\Entity;

use App\Repository\ExerciseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExerciseRepository::class)]
#[ORM\Table(name: 'exercises')]
class Exercise
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['workout_plan:read'])]
    private ?string $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    #[Groups(['workout_plan:read'])]
    private string $name;

    // Number of sets — e.g. 3
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['workout_plan:read'])]
    private ?int $sets = null;

    // Number of repetitions per set — e.g. 10
    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['workout_plan:read'])]
    private ?int $reps = null;

    // Optional free-text notes — e.g. "Keep back straight", "Use 20kg"
    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['workout_plan:read'])]
    private ?string $notes = null;

    // The day this exercise is scheduled on
    #[ORM\ManyToOne(targetEntity: WorkoutDay::class, inversedBy: 'exercises')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutDay $workoutDay;

    // ─── Getters & Setters ───────────────────────────────────────────────────

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSets(): ?int
    {
        return $this->sets;
    }

    public function setSets(?int $sets): static
    {
        $this->sets = $sets;
        return $this;
    }

    public function getReps(): ?int
    {
        return $this->reps;
    }

    public function setReps(?int $reps): static
    {
        $this->reps = $reps;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getWorkoutDay(): WorkoutDay
    {
        return $this->workoutDay;
    }

    public function setWorkoutDay(WorkoutDay $workoutDay): static
    {
        $this->workoutDay = $workoutDay;
        return $this;
    }
}
