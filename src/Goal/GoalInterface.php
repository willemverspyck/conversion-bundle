<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Goal;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Spyck\ConversionBundle\Entity\Target;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.conversion.goal'])]
interface GoalInterface
{
    /**
     * Get date.
     */
    public function getDate(): DateTimeInterface;

    /**
     * Set date.
     */
    public function setDate(DateTimeInterface $date): void;

    /**
     * Get data.
     */
    public function getData(): iterable;

    /**
     * Get entity name.
     */
    public function getEntity(): string;

    /**
     * If the goals are calculated with interval (or all data).
     */
    public function getInterval(): ?int;

    /**
     * Get parameters.
     *
     * @return array<int, ParameterInterface>
     */
    public function getParameters(): iterable;

    /**
     * Get targets.
     *
     * @return Collection<int, Target>
     */
    public function getTargets(): Collection;

    /**
     * Set targets.
     */
    public function setTargets(Collection $targets): void;
}
