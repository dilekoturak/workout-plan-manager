<?php

namespace App\Entity;

use App\Repository\WorkoutPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkoutPlanRepository::class)]
#[ORM\Table(name: 'workout_plans')]
#[ORM\HasLifecycleCallbacks]
class WorkoutPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 150)]
    private string $name;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // A plan is composed of multiple days (e.g. Monday, Wednesday, Friday)
    #[ORM\OneToMany(targetEntity: WorkoutDay::class, mappedBy: 'workoutPlan', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $workoutDays;

    // Tracks which users are assigned to this plan
    #[ORM\OneToMany(targetEntity: UserWorkoutPlan::class, mappedBy: 'workoutPlan', cascade: ['remove'])]
    private Collection $userWorkoutPlans;

    public function __construct()
    {
        $this->workoutDays      = new ArrayCollection();
        $this->userWorkoutPlans = new ArrayCollection();
        $this->createdAt        = new \DateTimeImmutable();
        $this->updatedAt        = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getWorkoutDays(): Collection
    {
        return $this->workoutDays;
    }

    public function addWorkoutDay(WorkoutDay $day): static
    {
        if (!$this->workoutDays->contains($day)) {
            $this->workoutDays->add($day);
            $day->setWorkoutPlan($this);
        }
        return $this;
    }

    public function removeWorkoutDay(WorkoutDay $day): static
    {
        $this->workoutDays->removeElement($day);
        return $this;
    }

    public function getUserWorkoutPlans(): Collection
    {
        return $this->userWorkoutPlans;
    }
}
