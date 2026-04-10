<?php

namespace App\Tests\Api;

/**
 * API tests for /api/workout-plans endpoints.
 *
 * Covers:
 *  - GET    /api/workout-plans                          → 200 list
 *  - POST   /api/workout-plans                          → 201 created with nested days/exercises
 *  - POST   /api/workout-plans                          → 422 validation error
 *  - GET    /api/workout-plans/{id}                     → 200 with days & exercises
 *  - GET    /api/workout-plans/{id}                     → 404 not found
 *  - PUT    /api/workout-plans/{id}                     → 200 full replacement
 *  - DELETE /api/workout-plans/{id}                     → 204 deleted
 *  - DELETE /api/workout-plans/{id}                     → 404 not found
 *  - POST   /api/workout-plans/{planId}/assign/{userId} → 201 assigned
 *  - POST   /api/workout-plans/{planId}/assign/{userId} → 409 already assigned
 *  - POST   /api/workout-plans/{planId}/assign/{userId} → 404 unknown user/plan
 *  - DELETE /api/workout-plans/{planId}/assign/{userId} → 204 unassigned
 */
class WorkoutPlanApiTest extends ApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/workout-plans
    // -------------------------------------------------------------------------

    public function testListPlansReturnsEmptyArrayInitially(): void
    {
        $response = $this->getJson('/api/workout-plans');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $this->json($response));
    }

    // -------------------------------------------------------------------------
    // POST /api/workout-plans
    // -------------------------------------------------------------------------

    public function testCreatePlanReturns201WithNestedStructure(): void
    {
        $response = $this->postJson('/api/workout-plans', [
            'name' => 'Push Pull Legs',
            'days' => [
                [
                    'name'      => 'Push Day',
                    'exercises' => [
                        ['name' => 'Bench Press', 'sets' => 4, 'reps' => 8, 'notes' => 'Control the descent'],
                        ['name' => 'Overhead Press', 'sets' => 3, 'reps' => 10, 'notes' => null],
                    ],
                ],
                [
                    'name'      => 'Pull Day',
                    'exercises' => [
                        ['name' => 'Pull Up', 'sets' => 4, 'reps' => 6, 'notes' => null],
                    ],
                ],
            ],
        ]);

        $data = $this->json($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Push Pull Legs', $data['name']);
        $this->assertCount(2, $data['workoutDays']);

        // Verify first day and its exercises
        $days = array_values($data['workoutDays']);
        $this->assertSame('Push Day', $days[0]['name']);
        $this->assertCount(2, $days[0]['exercises']);
        $this->assertSame('Bench Press', $days[0]['exercises'][0]['name']);
        $this->assertSame(4, $days[0]['exercises'][0]['sets']);
        $this->assertSame(8, $days[0]['exercises'][0]['reps']);
        $this->assertSame('Control the descent', $days[0]['exercises'][0]['notes']);
    }

    public function testCreatePlanReturns422WhenNameMissing(): void
    {
        $response = $this->postJson('/api/workout-plans', [
            'name' => '',
            'days' => [],
        ]);

        $data = $this->json($response);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
    }

    // -------------------------------------------------------------------------
    // GET /api/workout-plans/{id}
    // -------------------------------------------------------------------------

    public function testGetPlanByIdReturnsFullStructure(): void
    {
        $id = $this->createPlan('My Plan');

        $response = $this->getJson('/api/workout-plans/' . $id);
        $data     = $this->json($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id, $data['id']);
        $this->assertSame('My Plan', $data['name']);
        $this->assertArrayHasKey('workoutDays', $data);
    }

    public function testGetPlanByIdReturns404ForUnknownId(): void
    {
        $response = $this->getJson('/api/workout-plans/00000000-0000-0000-0000-000000000000');

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // PUT /api/workout-plans/{id}
    // -------------------------------------------------------------------------

    public function testUpdatePlanReturns200AndReplacesDays(): void
    {
        $id = $this->createPlan('Original Name');

        $response = $this->putJson('/api/workout-plans/' . $id, [
            'name' => 'Updated Plan',
            'days' => [
                [
                    'name'      => 'New Day',
                    'exercises' => [
                        ['name' => 'Lunges', 'sets' => 3, 'reps' => 12, 'notes' => null],
                    ],
                ],
            ],
        ]);

        $data = $this->json($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Updated Plan', $data['name']);

        // The original "Monday" day should be replaced by "New Day"
        $days = array_values($data['workoutDays']);
        $this->assertCount(1, $days);
        $this->assertSame('New Day', $days[0]['name']);
        $this->assertSame('Lunges', $days[0]['exercises'][0]['name']);
    }

    public function testUpdatePlanReturns404ForUnknownId(): void
    {
        $response = $this->putJson('/api/workout-plans/00000000-0000-0000-0000-000000000000', [
            'name' => 'Ghost Plan',
            'days' => [],
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DELETE /api/workout-plans/{id}
    // -------------------------------------------------------------------------

    public function testDeletePlanReturns204(): void
    {
        $id = $this->createPlan();

        $response = $this->deleteJson('/api/workout-plans/' . $id);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeletedPlanIsNoLongerReachable(): void
    {
        $id = $this->createPlan();
        $this->deleteJson('/api/workout-plans/' . $id);

        $response = $this->getJson('/api/workout-plans/' . $id);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeletePlanReturns404ForUnknownId(): void
    {
        $response = $this->deleteJson('/api/workout-plans/00000000-0000-0000-0000-000000000000');

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // POST /api/workout-plans/{planId}/assign/{userId}
    // -------------------------------------------------------------------------

    public function testAssignUserReturns201WithAssignedAt(): void
    {
        $planId = $this->createPlan();
        $userId = $this->createUser();

        $response = $this->postJson("/api/workout-plans/$planId/assign/$userId", []);
        $data     = $this->json($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('assignedAt', $data);
        $this->assertSame('User successfully assigned to the workout plan.', $data['message']);
    }

    public function testAssignUserReturns409WhenAlreadyAssigned(): void
    {
        $planId = $this->createPlan();
        $userId = $this->createUser();

        $this->postJson("/api/workout-plans/$planId/assign/$userId", []);
        $response = $this->postJson("/api/workout-plans/$planId/assign/$userId", []);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testAssignUserReturns404ForUnknownPlan(): void
    {
        $userId = $this->createUser();

        $response = $this->postJson('/api/workout-plans/00000000-0000-0000-0000-000000000000/assign/' . $userId, []);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testAssignUserReturns404ForUnknownUser(): void
    {
        $planId = $this->createPlan();

        $response = $this->postJson("/api/workout-plans/$planId/assign/00000000-0000-0000-0000-000000000000", []);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DELETE /api/workout-plans/{planId}/assign/{userId}
    // -------------------------------------------------------------------------

    public function testUnassignUserReturns204(): void
    {
        $planId = $this->createPlan();
        $userId = $this->createUser();

        $this->postJson("/api/workout-plans/$planId/assign/$userId", []);
        $response = $this->deleteJson("/api/workout-plans/$planId/assign/$userId");

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testUnassignUserReturns404WhenNotAssigned(): void
    {
        $planId = $this->createPlan();
        $userId = $this->createUser();

        $response = $this->deleteJson("/api/workout-plans/$planId/assign/$userId");

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // Integration: assign → delete plan → check plan is gone
    // -------------------------------------------------------------------------

    public function testDeletePlanWithAssignedUserRemovesAssignment(): void
    {
        $planId = $this->createPlan();
        $userId = $this->createUser();

        $this->postJson("/api/workout-plans/$planId/assign/$userId", []);
        $this->deleteJson('/api/workout-plans/' . $planId);

        // Plan is gone
        $this->assertSame(404, $this->getJson('/api/workout-plans/' . $planId)->getStatusCode());

        // User still exists
        $this->assertSame(200, $this->getJson('/api/users/' . $userId)->getStatusCode());
    }
}
