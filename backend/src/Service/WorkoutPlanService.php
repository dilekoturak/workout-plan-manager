<?php

namespace App\Service;

use App\DTO\WorkoutPlanDTO;
use App\Entity\Exercise;
use App\Entity\UserWorkoutPlan;
use App\Entity\WorkoutDay;
use App\Entity\WorkoutPlan;
use App\Exception\UserAlreadyAssignedException;
use App\Exception\UserNotFoundException;
use App\Exception\WorkoutPlanNotFoundException;
use App\Repository\UserRepository;
use App\Repository\UserWorkoutPlanRepository;
use App\Repository\WorkoutPlanRepository;
use Doctrine\ORM\EntityManagerInterface;

class WorkoutPlanService
{
    public function __construct(
        private readonly WorkoutPlanRepository    $workoutPlanRepository,
        private readonly UserRepository           $userRepository,
        private readonly UserWorkoutPlanRepository $userWorkoutPlanRepository,
        private readonly EntityManagerInterface   $entityManager,
    ) {}

    /** @return WorkoutPlan[] */
    public function findAll(): array
    {
        return $this->workoutPlanRepository->findAll();
    }

    public function findOne(string $id): WorkoutPlan
    {
        $plan = $this->workoutPlanRepository->findWithDaysAndExercises($id);

        if ($plan === null) {
            throw new WorkoutPlanNotFoundException($id);
        }

        return $plan;
    }

    public function create(WorkoutPlanDTO $dto): WorkoutPlan
    {
        $plan = new WorkoutPlan();
        $plan->setName($dto->name);

        foreach ($dto->days as $dayDTO) {
            $day = new WorkoutDay();
            $day->setName($dayDTO->name);

            foreach ($dayDTO->exercises as $exerciseDTO) {
                $exercise = new Exercise();
                $exercise->setName($exerciseDTO->name);
                $exercise->setSets($exerciseDTO->sets);
                $exercise->setReps($exerciseDTO->reps);
                $exercise->setNotes($exerciseDTO->notes);
                $day->addExercise($exercise);
            }

            $plan->addWorkoutDay($day);
        }

        $this->entityManager->persist($plan);
        $this->entityManager->flush();

        return $plan;
    }

    // On update: replace all days and exercises with the new ones from the DTO.
    // orphanRemoval=true on the collections handles deleting the old records automatically.
    // After saving, all assigned users must be notified — mail step will hook in here later.
    public function update(string $id, WorkoutPlanDTO $dto): WorkoutPlan
    {
        $plan = $this->findOne($id);
        $plan->setName($dto->name);

        // Remove all existing days (orphanRemoval cascades to exercises)
        foreach ($plan->getWorkoutDays() as $existingDay) {
            $plan->removeWorkoutDay($existingDay);
        }

        // Add the new set of days and exercises from the DTO
        foreach ($dto->days as $dayDTO) {
            $day = new WorkoutDay();
            $day->setName($dayDTO->name);

            foreach ($dayDTO->exercises as $exerciseDTO) {
                $exercise = new Exercise();
                $exercise->setName($exerciseDTO->name);
                $exercise->setSets($exerciseDTO->sets);
                $exercise->setReps($exerciseDTO->reps);
                $exercise->setNotes($exerciseDTO->notes);
                $day->addExercise($exercise);
            }

            $plan->addWorkoutDay($day);
        }

        $this->entityManager->flush();

        // TODO: notify assigned users by email (Step 6)

        return $plan;
    }

    // On delete: all assigned users must be notified before the plan is removed.
    // Mail step will hook in here later.
    public function delete(string $id): void
    {
        $plan = $this->findOne($id);

        // TODO: notify assigned users by email before deletion (Step 6)

        $this->entityManager->remove($plan);
        $this->entityManager->flush();
    }

    // Assigns a user to a plan. Throws if already assigned.
    // After assigning, the user must receive a confirmation email — mail step hooks in here.
    public function assignUser(string $planId, string $userId): UserWorkoutPlan
    {
        $plan = $this->findOne($planId);

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new UserNotFoundException($userId);
        }

        $existing = $this->userWorkoutPlanRepository->findByUserAndPlan($user, $plan);
        if ($existing !== null) {
            throw new UserAlreadyAssignedException($userId, $planId);
        }

        $assignment = new UserWorkoutPlan();
        $assignment->setUser($user);
        $assignment->setWorkoutPlan($plan);

        $this->entityManager->persist($assignment);
        $this->entityManager->flush();

        // TODO: send assignment confirmation email to user (Step 6)

        return $assignment;
    }

    // Removes a user assignment from a plan.
    public function unassignUser(string $planId, string $userId): void
    {
        $plan = $this->findOne($planId);

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new UserNotFoundException($userId);
        }

        $assignment = $this->userWorkoutPlanRepository->findByUserAndPlan($user, $plan);
        if ($assignment === null) {
            throw new \DomainException(sprintf(
                'User "%s" is not assigned to workout plan "%s".',
                $userId,
                $planId
            ));
        }

        $this->entityManager->remove($assignment);
        $this->entityManager->flush();
    }
}
