<?php

namespace App\Exception;

class ConflictException extends \RuntimeException
{
    public function __construct(string $resourceType, string $field, string $value)
    {
        parent::__construct(sprintf('%s with %s "%s" already exists.', $resourceType, $field, $value));
    }
}
