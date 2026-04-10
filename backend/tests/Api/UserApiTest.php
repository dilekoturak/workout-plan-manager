<?php

namespace App\Tests\Api;

/**
 * API tests for /api/users endpoints.
 *
 * Covers:
 *  - GET    /api/users              → 200 list
 *  - POST   /api/users              → 201 created
 *  - POST   /api/users              → 422 validation error
 *  - POST   /api/users              → 409 duplicate email
 *  - GET    /api/users/{id}         → 200 single user
 *  - GET    /api/users/{id}         → 404 not found
 *  - PUT    /api/users/{id}         → 200 updated
 *  - PUT    /api/users/{id}         → 409 email taken by another user
 *  - DELETE /api/users/{id}         → 204 deleted
 *  - DELETE /api/users/{id}         → 404 not found
 */
class UserApiTest extends ApiTestCase
{
    // -------------------------------------------------------------------------
    // GET /api/users
    // -------------------------------------------------------------------------

    public function testListUsersReturnsEmptyArrayInitially(): void
    {
        $response = $this->getJson('/api/users');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $this->json($response));
    }

    public function testListUsersReturnsCreatedUsers(): void
    {
        $this->createUser('Alice', 'Smith', 'alice@example.com');
        $this->createUser('Bob', 'Jones', 'bob@example.com');

        $response = $this->getJson('/api/users');
        $data     = $this->json($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $data);
    }

    // -------------------------------------------------------------------------
    // POST /api/users
    // -------------------------------------------------------------------------

    public function testCreateUserReturns201WithCorrectFields(): void
    {
        $response = $this->postJson('/api/users', [
            'firstName' => 'Jane',
            'lastName'  => 'Doe',
            'email'     => 'jane@example.com',
        ]);

        $data = $this->json($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Jane', $data['firstName']);
        $this->assertSame('Doe', $data['lastName']);
        $this->assertSame('jane@example.com', $data['email']);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testCreateUserReturns422WhenFieldsMissing(): void
    {
        $response = $this->postJson('/api/users', [
            'firstName' => '',
            'lastName'  => '',
            'email'     => 'not-an-email',
        ]);

        $data = $this->json($response);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertArrayHasKey('errors', $data);
        $this->assertArrayHasKey('firstName', $data['errors']);
        $this->assertArrayHasKey('email', $data['errors']);
    }

    public function testCreateUserReturns409OnDuplicateEmail(): void
    {
        $this->createUser('Alice', 'Smith', 'alice@example.com');

        $response = $this->postJson('/api/users', [
            'firstName' => 'Other',
            'lastName'  => 'Person',
            'email'     => 'alice@example.com',
        ]);

        $this->assertSame(409, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // GET /api/users/{id}
    // -------------------------------------------------------------------------

    public function testGetUserByIdReturns200(): void
    {
        $id = $this->createUser();

        $response = $this->getJson('/api/users/' . $id);
        $data     = $this->json($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id, $data['id']);
    }

    public function testGetUserByIdReturns404ForUnknownId(): void
    {
        $response = $this->getJson('/api/users/00000000-0000-0000-0000-000000000000');

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // PUT /api/users/{id}
    // -------------------------------------------------------------------------

    public function testUpdateUserReturns200WithNewData(): void
    {
        $id = $this->createUser('John', 'Doe', 'john@example.com');

        $response = $this->putJson('/api/users/' . $id, [
            'firstName' => 'Johnny',
            'lastName'  => 'Updated',
            'email'     => 'johnny@example.com',
        ]);

        $data = $this->json($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Johnny', $data['firstName']);
        $this->assertSame('johnny@example.com', $data['email']);
    }

    public function testUpdateUserReturns409WhenEmailBelongsToAnotherUser(): void
    {
        $this->createUser('Alice', 'Smith', 'alice@example.com');
        $bobId = $this->createUser('Bob', 'Jones', 'bob@example.com');

        // Try to update Bob's email to Alice's email
        $response = $this->putJson('/api/users/' . $bobId, [
            'firstName' => 'Bob',
            'lastName'  => 'Jones',
            'email'     => 'alice@example.com',
        ]);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testUpdateUserReturns404ForUnknownId(): void
    {
        $response = $this->putJson('/api/users/00000000-0000-0000-0000-000000000000', [
            'firstName' => 'X',
            'lastName'  => 'Y',
            'email'     => 'x@example.com',
        ]);

        $this->assertSame(404, $response->getStatusCode());
    }

    // -------------------------------------------------------------------------
    // DELETE /api/users/{id}
    // -------------------------------------------------------------------------

    public function testDeleteUserReturns204(): void
    {
        $id = $this->createUser();

        $response = $this->deleteJson('/api/users/' . $id);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeletedUserIsNoLongerReachable(): void
    {
        $id = $this->createUser();
        $this->deleteJson('/api/users/' . $id);

        $response = $this->getJson('/api/users/' . $id);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testDeleteUserReturns404ForUnknownId(): void
    {
        $response = $this->deleteJson('/api/users/00000000-0000-0000-0000-000000000000');

        $this->assertSame(404, $response->getStatusCode());
    }
}
