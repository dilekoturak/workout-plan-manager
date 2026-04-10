<?php

namespace App\Exception;

class WorkoutPlanNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Workout plan with ID "%s" was not found.', $id));
    }
}
