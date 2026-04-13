<?php

namespace App\Message;

final class UserAssignedMessage
{
    public function __construct(
        public readonly string $userId,
        public readonly string $planId,
    ) {}
}
