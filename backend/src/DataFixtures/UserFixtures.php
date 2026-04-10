<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates sample users for local development.
 * Run with: make fixtures
 */
class UserFixtures extends Fixture
{
    // Reference keys — used by WorkoutPlanFixtures to link assignments
    public const REF_ALICE = 'user-alice';
    public const REF_BOB   = 'user-bob';
    public const REF_CAROL = 'user-carol';

    public function load(ObjectManager $manager): void
    {
        $users = [
            [self::REF_ALICE, 'Alice', 'Johnson', 'alice@example.com'],
            [self::REF_BOB,   'Bob',   'Smith',   'bob@example.com'],
            [self::REF_CAROL, 'Carol', 'Williams','carol@example.com'],
        ];

        foreach ($users as [$ref, $firstName, $lastName, $email]) {
            $user = new User();
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);

            $manager->persist($user);
            $this->addReference($ref, $user);
        }

        $manager->flush();
    }
}
