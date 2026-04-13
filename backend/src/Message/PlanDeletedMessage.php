<?php

namespace App\Message;

final class PlanDeletedMessage
{
    public function __construct(
        public readonly string $planName,
        /** @var string[] List of user email addresses to notify */
        public readonly array $userEmails,
    ) {}
}
