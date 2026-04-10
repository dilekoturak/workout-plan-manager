<?php

namespace App\Exception;

// Thrown when a User cannot be found by the given ID.
// The controller catches this and returns a clean 404 JSON response.
// Equivalent to a custom NotFoundException in .NET.
class UserNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('User with ID "%s" was not found.', $id));
    }
}
