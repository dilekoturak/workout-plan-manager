<?php

namespace App\Service;

use App\DTO\WorkoutPlanDTO;
use App\Entity\Exercise;
use App\Entity\UserWorkoutPlan;
use App\Entity\WorkoutDay;
use App\Entity\WorkoutPlan;
use App\Message\PlanDeletedMessage;
use App\Message\PlanModifiedMessage;
use App\Message\UserAssignedMessage;
use App\Repository\UserRepository;
use App\Repository\UserWorkoutPlanRepository;
use App\Repository\WorkoutPlanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WorkoutPlanService
{
    public function __construct(
        private readonly WorkoutPlanRepository     $workoutPlanRepository,
        private readonly UserRepository            $userRepository,
        private readonly UserWorkoutPlanRepository $userWorkoutPlanRepository,
        private readonly EntityManagerInterface    $entityManager,
        private readonly MessageBusInterface       $bus,
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
            throw new \RuntimeException(sprintf('Workout plan with ID "%s" was not found.', $id), 404);
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

    public function update(string $id, WorkoutPlanDTO $dto): WorkoutPlan
    {
        $plan = $this->findOne($id);
        $plan->setName($dto->name);

        foreach ($plan->getWorkoutDays() as $existingDay) {
            $plan->removeWorkoutDay($existingDay);
        }

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

        // Dispatch a message to RabbitMQ — the worker picks it up and sends emails to all assigned users
        $this->bus->dispatch(new PlanModifiedMessage($plan->getId()));

        return $plan;
    }

    public function delete(string $id): void
    {
        $plan = $this->findOne($id);
        $assignments = $this->userWorkoutPlanRepository->findByWorkoutPlan($plan);
        $userEmails  = array_map(
            fn(UserWorkoutPlan $a) => $a->getUser()->getEmail(),
            $assignments
        );

        $planName = $plan->getName();

        $this->entityManager->remove($plan);
        $this->entityManager->flush();

        // Dispatch deletion notification — user emails and plan name are embedded in the message
        if (!empty($userEmails)) {
            $this->bus->dispatch(new PlanDeletedMessage($planName, $userEmails));
        }
    }

    public function assignUser(string $planId, string $userId): UserWorkoutPlan
    {
        $plan = $this->findOne($planId);

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new \RuntimeException(sprintf('User with ID "%s" was not found.', $userId), 404);
        }

        $existing = $this->userWorkoutPlanRepository->findByUserAndPlan($user, $plan);
        if ($existing !== null) {
            throw new \RuntimeException(sprintf('User "%s" is already assigned to workout plan "%s".', $userId, $planId), 409);
        }

        $assignment = new UserWorkoutPlan();
        $assignment->setUser($user);
        $assignment->setWorkoutPlan($plan);

        $this->entityManager->persist($assignment);
        $this->entityManager->flush();

        // Dispatch assignment confirmation email — the worker sends it asynchronously
        $this->bus->dispatch(new UserAssignedMessage($user->getId(), $plan->getId()));

        return $assignment;
    }

    /** @return UserWorkoutPlan[] */
    public function getAssignedUsers(string $planId): array
    {
        $plan = $this->findOne($planId);
        return $this->userWorkoutPlanRepository->findByWorkoutPlan($plan);
    }

    public function unassignUser(string $planId, string $userId): void
    {
        $plan = $this->findOne($planId);

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new \RuntimeException(sprintf('User with ID "%s" was not found.', $userId), 404);
        }

        $assignment = $this->userWorkoutPlanRepository->findByUserAndPlan($user, $plan);
        if ($assignment === null) {
            throw new \RuntimeException(sprintf('User "%s" is not assigned to workout plan "%s".', $userId, $planId), 404);
        }

        $this->entityManager->remove($assignment);
        $this->entityManager->flush();
    }
}
