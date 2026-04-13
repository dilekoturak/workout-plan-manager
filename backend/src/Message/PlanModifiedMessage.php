<?php

namespace App\Message;

final class PlanModifiedMessage
{
    public function __construct(
        public readonly string $planId,
    ) {}
}
