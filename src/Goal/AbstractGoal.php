<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Goal;

use Spyck\ConversionBundle\Entity\Goal;

abstract class AbstractGoal implements GoalInterface
{
    private Goal $goal;

    public static function getName(): string
    {
        return static::class;
    }

    public function getGoal(): Goal
    {
        return $this->goal;
    }

    public function setGoal(Goal $goal): void
    {
        $this->goal = $goal;
    }

    public function getInterval(): ?int
    {
        return null;
    }

    public function getParameters(): iterable
    {
        return [];
    }
}
