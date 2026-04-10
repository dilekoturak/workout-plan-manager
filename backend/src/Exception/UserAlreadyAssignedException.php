<?php

namespace App\Exception;

// Thrown when trying to assign a user to a plan they are already assigned to.
class UserAlreadyAssignedException extends \RuntimeException
{
    public function __construct(string $userId, string $planId)
    {
        parent::__construct(sprintf(
            'User "%s" is already assigned to workout plan "%s".',
            $userId,
            $planId
        ));
    }
}
