<?php

namespace App\Repository;

use App\Entity\WorkoutPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WorkoutPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkoutPlan::class);
    }

    // Eagerly load days and their exercises in one query to avoid N+1 problem
    public function findWithDaysAndExercises(string $id): ?WorkoutPlan
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.workoutDays', 'd')
            ->addSelect('d')
            ->leftJoin('d.exercises', 'e')
            ->addSelect('e')
            ->where('p.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
