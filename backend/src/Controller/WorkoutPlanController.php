<?php

namespace App\Controller;

use App\DTO\WorkoutPlanDTO;
use App\Service\WorkoutPlanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/workout-plans', name: 'api_workout_plans_')]
class WorkoutPlanController extends AbstractController
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $plans = $this->workoutPlanService->findAll();

        return $this->json($plans, Response::HTTP_OK, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $plan = $this->workoutPlanService->findOne($id);

        return $this->json($plan, Response::HTTP_OK, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] WorkoutPlanDTO $dto): JsonResponse
    {
        $plan = $this->workoutPlanService->create($dto);

        return $this->json($plan, Response::HTTP_CREATED, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] WorkoutPlanDTO $dto): JsonResponse
    {
        $plan = $this->workoutPlanService->update($id, $dto);

        return $this->json($plan, Response::HTTP_OK, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $this->workoutPlanService->delete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─── User assignment endpoints ───────────────────────────────────────────

    #[Route('/{id}/users', name: 'list_users', methods: ['GET'])]
    public function listUsers(string $id): JsonResponse
    {
        $assignments = $this->workoutPlanService->getAssignedUsers($id);

        $result = array_map(fn($a) => [
            'userId'     => $a->getUser()->getId(),
            'firstName'  => $a->getUser()->getFirstName(),
            'lastName'   => $a->getUser()->getLastName(),
            'email'      => $a->getUser()->getEmail(),
            'assignedAt' => $a->getAssignedAt()->format(\DateTimeInterface::ATOM),
        ], $assignments);

        return $this->json($result, Response::HTTP_OK);
    }

    #[Route('/{planId}/assign/{userId}', name: 'assign_user', methods: ['POST'])]
    public function assignUser(string $planId, string $userId): JsonResponse
    {
        $assignment = $this->workoutPlanService->assignUser($planId, $userId);

        return $this->json([
            'message'    => 'User successfully assigned to the workout plan.',
            'assignedAt' => $assignment->getAssignedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{planId}/assign/{userId}', name: 'unassign_user', methods: ['DELETE'])]
    public function unassignUser(string $planId, string $userId): JsonResponse
    {
        $this->workoutPlanService->unassignUser($planId, $userId);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
