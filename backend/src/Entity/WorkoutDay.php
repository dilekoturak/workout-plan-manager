<?php

namespace App\Entity;

use App\Repository\WorkoutDayRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkoutDayRepository::class)]
#[ORM\Table(name: 'workout_days')]
class WorkoutDay
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['workout_plan:read'])]
    private ?string $id = null;

    // Day name is free text — e.g. "Monday", "Push Day", "Day 1"
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[Groups(['workout_plan:read'])]
    private string $name;

    // The plan this day belongs to
    #[ORM\ManyToOne(targetEntity: WorkoutPlan::class, inversedBy: 'workoutDays')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutPlan $workoutPlan;

    // The exercises scheduled for this day
    #[ORM\OneToMany(targetEntity: Exercise::class, mappedBy: 'workoutDay', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['workout_plan:read'])]
    private Collection $exercises;

    public function __construct()
    {
        $this->exercises = new ArrayCollection();
    }

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

    public function getWorkoutPlan(): WorkoutPlan
    {
        return $this->workoutPlan;
    }

    public function setWorkoutPlan(WorkoutPlan $workoutPlan): static
    {
        $this->workoutPlan = $workoutPlan;
        return $this;
    }

    public function getExercises(): array
    {
        return array_values($this->exercises->toArray());
    }

    public function addExercise(Exercise $exercise): static
    {
        if (!$this->exercises->contains($exercise)) {
            $this->exercises->add($exercise);
            $exercise->setWorkoutDay($this);
        }
        return $this;
    }

    public function removeExercise(Exercise $exercise): static
    {
        $this->exercises->removeElement($exercise);
        return $this;
    }
}
