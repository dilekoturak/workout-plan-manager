<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
