<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Goal;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Spyck\ConversionBundle\Entity\Target;

abstract class AbstractGoal implements GoalInterface
{
    private DateTimeInterface $date;
    private Collection $targets;

    public function setDate(DateTimeInterface $date): void
    {
        $this->date = $date;
    }

    public function getDate(): string
    {
        return $this->date->format('Y-m-d');
    }

    public function hasInterval(): bool
    {
        return true;
    }

    public function setTargets(Collection $targets): void
    {
        $this->targets = $targets;
    }

    /**
     * @return Collection<int, Target>
     */
    public function getTargets(): Collection
    {
        return $this->targets;
    }

    public function getParameters(): iterable
    {
        return [];
    }
}
