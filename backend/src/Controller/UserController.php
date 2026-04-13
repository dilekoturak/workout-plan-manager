<?php

namespace App\Controller;

use App\DTO\UserDTO;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userService->findAll();

        return $this->json($users, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->findOne($id);

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] UserDTO $dto): JsonResponse
    {
        $user = $this->userService->create($dto);

        return $this->json($user, Response::HTTP_CREATED, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] UserDTO $dto): JsonResponse
    {
        $user = $this->userService->update($id, $dto);

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $this->userService->delete($id);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
