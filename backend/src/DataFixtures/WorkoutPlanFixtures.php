<?php

namespace App\DataFixtures;

use App\Entity\Exercise;
use App\Entity\UserWorkoutPlan;
use App\Entity\WorkoutDay;
use App\Entity\WorkoutPlan;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Creates sample workout plans with days, exercises and user assignments.
 * Depends on UserFixtures (users must exist before assignment).
 * Run with: make fixtures
 */
class WorkoutPlanFixtures extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        // ── Plan 1: Push Pull Legs ───────────────────────────────────────────
        $ppl = $this->buildPlan('Push Pull Legs', [
            'Push Day' => [
                ['Bench Press',    4, 8,  'Control the descent'],
                ['Overhead Press', 3, 10, null],
                ['Tricep Dip',     3, 12, null],
            ],
            'Pull Day' => [
                ['Pull Up',      4, 6,  'Full range of motion'],
                ['Barbell Row',  4, 8,  null],
                ['Bicep Curl',   3, 12, null],
            ],
            'Leg Day' => [
                ['Squat',          4, 8,  'Keep back straight'],
                ['Romanian Deadlift', 3, 10, 'Hip hinge, not squat'],
                ['Leg Press',      3, 12, null],
            ],
        ]);
        $manager->persist($ppl);

        // ── Plan 2: Beginner Full Body ───────────────────────────────────────
        $fullBody = $this->buildPlan('Beginner Full Body', [
            'Monday' => [
                ['Squat',      3, 10, null],
                ['Bench Press', 3, 10, null],
                ['Deadlift',   3, 5,  'Use belt above 80%'],
            ],
            'Wednesday' => [
                ['Overhead Press', 3, 10, null],
                ['Pull Up',        3, 6,  null],
                ['Plank',          3, 60, 'Seconds, not reps'],
            ],
            'Friday' => [
                ['Squat',      3, 10, null],
                ['Bench Press', 3, 10, null],
                ['Barbell Row', 3, 10, null],
            ],
        ]);
        $manager->persist($fullBody);

        // ── Plan 3: Core & Cardio ────────────────────────────────────────────
        $core = $this->buildPlan('Core & Cardio', [
            'Day 1' => [
                ['Plank',         3, 60, 'Seconds'],
                ['Bicycle Crunch', 3, 20, null],
                ['Treadmill Run', 1, 20, '20 minutes at steady pace'],
            ],
            'Day 2' => [
                ['Dead Bug',     3, 10, null],
                ['Russian Twist', 3, 20, null],
                ['Jump Rope',    3, 60, 'Seconds'],
            ],
        ]);
        $manager->persist($core);

        $manager->flush();

        // ── Assignments ──────────────────────────────────────────────────────
        /** @var \App\Entity\User $alice */
        $alice = $this->getReference(UserFixtures::REF_ALICE, \App\Entity\User::class);
        /** @var \App\Entity\User $bob */
        $bob   = $this->getReference(UserFixtures::REF_BOB, \App\Entity\User::class);
        /** @var \App\Entity\User $carol */
        $carol = $this->getReference(UserFixtures::REF_CAROL, \App\Entity\User::class);

        // Alice is on Push Pull Legs
        $manager->persist($this->assign($alice, $ppl));

        // Bob is on Beginner Full Body and Core & Cardio
        $manager->persist($this->assign($bob, $fullBody));
        $manager->persist($this->assign($bob, $core));

        // Carol is on Push Pull Legs and Beginner Full Body
        $manager->persist($this->assign($carol, $ppl));
        $manager->persist($this->assign($carol, $fullBody));

        $manager->flush();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * @param array<string, array<array{string, int|null, int|null, string|null}>> $days
     */
    private function buildPlan(string $name, array $days): WorkoutPlan
    {
        $plan = new WorkoutPlan();
        $plan->setName($name);

        foreach ($days as $dayName => $exercises) {
            $day = new WorkoutDay();
            $day->setName($dayName);

            foreach ($exercises as [$exName, $sets, $reps, $notes]) {
                $exercise = new Exercise();
                $exercise->setName($exName);
                $exercise->setSets($sets);
                $exercise->setReps($reps);
                $exercise->setNotes($notes);
                $day->addExercise($exercise);
            }

            $plan->addWorkoutDay($day);
        }

        return $plan;
    }

    private function assign(\App\Entity\User $user, WorkoutPlan $plan): UserWorkoutPlan
    {
        $assignment = new UserWorkoutPlan();
        $assignment->setUser($user);
        $assignment->setWorkoutPlan($plan);
        return $assignment;
    }
}
