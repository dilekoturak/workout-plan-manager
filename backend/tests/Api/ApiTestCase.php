<?php

namespace App\Tests\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for all API functional tests.
 *
 * Boots the Symfony kernel in the "test" environment (uses workout_plan_manager_test DB).
 * Truncates all application tables before each test for a clean slate.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    /** Tables to truncate before each test, in FK-safe order (children first). */
    private const TABLES = [
        'exercises',
        'workout_days',
        'user_workout_plans',
        'workout_plans',
        'users',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $conn = static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getConnection();

        // Temporarily disable FK checks so we can truncate in any order
        $conn->executeStatement('SET session_replication_role = replica');
        foreach (self::TABLES as $table) {
            $conn->executeStatement("TRUNCATE TABLE $table CASCADE");
        }
        $conn->executeStatement('SET session_replication_role = DEFAULT');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    protected function postJson(string $uri, array $data): Response
    {
        $this->client->request('POST', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        return $this->client->getResponse();
    }

    protected function putJson(string $uri, array $data): Response
    {
        $this->client->request('PUT', $uri, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));
        return $this->client->getResponse();
    }

    protected function getJson(string $uri): Response
    {
        $this->client->request('GET', $uri);
        return $this->client->getResponse();
    }

    protected function deleteJson(string $uri): Response
    {
        $this->client->request('DELETE', $uri);
        return $this->client->getResponse();
    }

    protected function json(Response $response): array
    {
        return json_decode($response->getContent(), true);
    }

    /**
     * Creates a user via the API and returns its UUID.
     */
    protected function createUser(
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $email = 'john@example.com',
    ): string {
        $response = $this->postJson('/api/users', [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
        ]);

        $this->assertSame(201, $response->getStatusCode(), 'createUser helper failed: ' . $response->getContent());

        return $this->json($response)['id'];
    }

    /**
     * Creates a workout plan via the API and returns its UUID.
     */
    protected function createPlan(string $name = 'Test Plan'): string
    {
        $response = $this->postJson('/api/workout-plans', [
            'name' => $name,
            'days' => [
                [
                    'name'      => 'Monday',
                    'exercises' => [
                        ['name' => 'Squat', 'sets' => 3, 'reps' => 10, 'notes' => null],
                    ],
                ],
            ],
        ]);

        $this->assertSame(201, $response->getStatusCode(), 'createPlan helper failed: ' . $response->getContent());

        return $this->json($response)['id'];
    }
}
