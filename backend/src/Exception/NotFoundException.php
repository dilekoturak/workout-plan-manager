<?php

namespace App\Exception;

class NotFoundException extends \RuntimeException
{
    public function __construct(string $resourceType, string $id)
    {
        parent::__construct(sprintf('%s with ID "%s" was not found.', $resourceType, $id));
    }
}
