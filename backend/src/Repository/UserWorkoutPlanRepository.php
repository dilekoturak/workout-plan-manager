<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserWorkoutPlan;
use App\Entity\WorkoutPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserWorkoutPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserWorkoutPlan::class);
    }

    public function findByUserAndPlan(User $user, WorkoutPlan $plan): ?UserWorkoutPlan
    {
        return $this->findOneBy([
            'user'        => $user,
            'workoutPlan' => $plan,
        ]);
    }

    // Returns all users assigned to a given plan — used for sending notifications
    /** @return UserWorkoutPlan[] */
    public function findByWorkoutPlan(WorkoutPlan $plan): array
    {
        return $this->findBy(['workoutPlan' => $plan]);
    }
}
