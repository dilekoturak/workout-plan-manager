<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

// UserService contains all business logic related to users.
// The controller never touches the EntityManager or Repository directly — it only calls this service.
// Think of this as the equivalent of a .NET service class injected into a controller.
class UserService
{
    public function __construct(
        private readonly UserRepository       $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /** @return User[] */
    public function findAll(): array
    {
        return $this->userRepository->findAll();
    }

    public function findOne(string $id): User
    {
        $user = $this->userRepository->find($id);

        if ($user === null) {
            throw new UserNotFoundException($id);
        }

        return $user;
    }

    public function create(UserDTO $dto): User
    {
        if ($this->userRepository->findByEmail($dto->email) !== null) {
            throw new \DomainException(sprintf('A user with email "%s" already exists.', $dto->email));
        }

        $user = new User();
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(string $id, UserDTO $dto): User
    {
        $user = $this->findOne($id);

        // Allow keeping the same email, but reject if another user already owns the new email
        $existingWithEmail = $this->userRepository->findByEmail($dto->email);
        if ($existingWithEmail !== null && $existingWithEmail->getId() !== $user->getId()) {
            throw new \DomainException(sprintf('A user with email "%s" already exists.', $dto->email));
        }

        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setEmail($dto->email);

        $this->entityManager->flush();

        return $user;
    }

    public function delete(string $id): void
    {
        $user = $this->findOne($id);

        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }
}
