<?php

namespace App\Entity;

use App\Repository\UserWorkoutPlanRepository;
use Doctrine\ORM\Mapping as ORM;

// This is the junction entity between User and WorkoutPlan.
// It replaces a raw many-to-many join table so we can store extra data (assignedAt).
// Think of it like a bridge table in .NET with additional payload columns.
#[ORM\Entity(repositoryClass: UserWorkoutPlanRepository::class)]
#[ORM\Table(name: 'user_workout_plans')]
#[ORM\UniqueConstraint(name: 'uq_user_workout_plan', columns: ['user_id', 'workout_plan_id'])]
class UserWorkoutPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userWorkoutPlans')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: WorkoutPlan::class, inversedBy: 'userWorkoutPlans')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private WorkoutPlan $workoutPlan;

    // Records when the user was assigned to this plan — useful for mail notifications and audit
    #[ORM\Column]
    private \DateTimeImmutable $assignedAt;

    public function __construct()
    {
        $this->assignedAt = new \DateTimeImmutable();
    }

    // ─── Getters & Setters ───────────────────────────────────────────────────

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
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

    public function getAssignedAt(): \DateTimeImmutable
    {
        return $this->assignedAt;
    }
}
