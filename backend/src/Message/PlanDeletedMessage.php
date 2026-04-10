<?php

namespace App\Message;

// Dispatched just before a workout plan is deleted.
// The handler notifies all assigned users that the plan has been removed.
// Note: the plan name is passed directly because the plan will be gone by the time
// the worker processes this message asynchronously.
final class PlanDeletedMessage
{
    public function __construct(
        public readonly string $planName,
        /** @var string[] List of user email addresses to notify */
        public readonly array $userEmails,
    ) {}
}
