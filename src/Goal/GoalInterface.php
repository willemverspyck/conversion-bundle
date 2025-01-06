<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Goal;

use Spyck\ConversionBundle\Entity\Goal;
use Spyck\ConversionBundle\Parameter\ParameterInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.conversion.goal'])]
interface GoalInterface
{
    public function getGoal(): Goal;

    public function setGoal(Goal $goal): void;

    public function getData(): iterable;

    public function getEntity(): string;

    public function getInterval(): ?int;

    /**
     * @return array<int, ParameterInterface>
     */
    public function getParameters(): iterable;
}
