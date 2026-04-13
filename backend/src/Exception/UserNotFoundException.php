<?php

namespace App\Exception;

// Thrown when a User cannot be found by the given ID.
class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('User with ID "%s" was not found.', $id));
    }
}
