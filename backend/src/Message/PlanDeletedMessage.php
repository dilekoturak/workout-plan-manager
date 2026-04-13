<?php

namespace App\Message;

final class PlanDeletedMessage
{
    public function __construct(
        public readonly string $planName,
        public readonly array $userEmails,
    ) {}
}
