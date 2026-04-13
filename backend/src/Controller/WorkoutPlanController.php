<?php

namespace App\Controller;

use App\DTO\ExerciseDTO;
use App\DTO\WorkoutDayDTO;
use App\DTO\WorkoutPlanDTO;
use App\Exception\UserAlreadyAssignedException;
use App\Exception\UserNotFoundException;
use App\Exception\WorkoutPlanNotFoundException;
use App\Service\WorkoutPlanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/workout-plans', name: 'api_workout_plans_')]
class WorkoutPlanController extends AbstractController
{
    public function __construct(
        private readonly WorkoutPlanService $workoutPlanService,
        private readonly ValidatorInterface $validator,
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
        try {
            $plan = $this->workoutPlanService->findOne($id);
        } catch (WorkoutPlanNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($plan, Response::HTTP_OK, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $dto = $this->deserializeAndValidate($request);

        if ($dto instanceof JsonResponse) {
            return $dto;
        }

        $plan = $this->workoutPlanService->create($dto);

        return $this->json($plan, Response::HTTP_CREATED, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $dto = $this->deserializeAndValidate($request);

        if ($dto instanceof JsonResponse) {
            return $dto;
        }

        try {
            $plan = $this->workoutPlanService->update($id, $dto);
        } catch (WorkoutPlanNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json($plan, Response::HTTP_OK, [], ['groups' => ['workout_plan:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        try {
            $this->workoutPlanService->delete($id);
        } catch (WorkoutPlanNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─── User assignment endpoints ───────────────────────────────────────────

    #[Route('/{id}/users', name: 'list_users', methods: ['GET'])]
    public function listUsers(string $id): JsonResponse
    {
        try {
            $assignments = $this->workoutPlanService->getAssignedUsers($id);
        } catch (WorkoutPlanNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

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
        try {
            $assignment = $this->workoutPlanService->assignUser($planId, $userId);
        } catch (WorkoutPlanNotFoundException | UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (UserAlreadyAssignedException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json([
            'message'    => 'User successfully assigned to the workout plan.',
            'assignedAt' => $assignment->getAssignedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{planId}/assign/{userId}', name: 'unassign_user', methods: ['DELETE'])]
    public function unassignUser(string $planId, string $userId): JsonResponse
    {
        try {
            $this->workoutPlanService->unassignUser($planId, $userId);
        } catch (WorkoutPlanNotFoundException | UserNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function deserializeAndValidate(Request $request): WorkoutPlanDTO|JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $dayDTOs = [];
        foreach ($data['days'] ?? [] as $dayData) {
            $exerciseDTOs = [];
            foreach ($dayData['exercises'] ?? [] as $exerciseData) {
                $exerciseDTOs[] = new ExerciseDTO(
                    name:  $exerciseData['name']  ?? '',
                    sets:  isset($exerciseData['sets'])  ? (int) $exerciseData['sets']  : null,
                    reps:  isset($exerciseData['reps'])  ? (int) $exerciseData['reps']  : null,
                    notes: $exerciseData['notes'] ?? null,
                );
            }
            $dayDTOs[] = new WorkoutDayDTO(
                name:      $dayData['name'] ?? '',
                exercises: $exerciseDTOs,
            );
        }

        $dto = new WorkoutPlanDTO(
            name: $data['name'] ?? '',
            days: $dayDTOs,
        );

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $dto;
    }
}
