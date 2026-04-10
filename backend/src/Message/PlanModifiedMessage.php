<?php

namespace App\Message;

// Dispatched when a workout plan is updated.
// The handler will notify all currently assigned users by email.
final class PlanModifiedMessage
{
    public function __construct(
        public readonly string $planId,
    ) {}
}
