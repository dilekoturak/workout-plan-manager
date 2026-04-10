<?php

namespace App\Message;

// Dispatched when a user is assigned to a workout plan.
// The handler will send a confirmation email to the user.
//
// Think of this like an Event in .NET — a plain data object that describes what happened.
// It carries only the IDs needed — the handler fetches the full entities from the DB.
final class UserAssignedMessage
{
    public function __construct(
        public readonly string $userId,
        public readonly string $planId,
    ) {}
}
