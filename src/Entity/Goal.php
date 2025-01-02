<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Spyck\ConversionBundle\Repository\GoalRepository;
use Stringable;

#[Doctrine\Entity(repositoryClass: GoalRepository::class)]
#[Doctrine\Table(name: 'conversion_goal')]
class Goal implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128)]
    private string $name;

    #[Doctrine\Column(name: 'adapter', type: Types::STRING, length: 128)]
    private string $adapter;

    #[Doctrine\Column(name: 'type', type: Types::STRING, length: 32)]
    private string $type;

    #[Doctrine\Column(name: 'date_min', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateMin = null;

    #[Doctrine\Column(name: 'date_max', type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateMax = null;

    #[Doctrine\Column(name: 'active', type: Types::BOOLEAN)]
    private bool $active;

    /**
     * @var Collection<int, Target>
     */
    #[Doctrine\OneToMany(targetEntity: Target::class, mappedBy: 'goal', cascade: ['persist'], orphanRemoval: true)]
    private Collection $targets;

    public function __construct()
    {
        $this->targets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAdapter(): string
    {
        return $this->adapter;
    }

    public function setAdapter(string $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateMin(): ?DateTimeImmutable
    {
        return $this->dateMin;
    }

    public function setDateMin(?DateTimeImmutable $dateMin): self
    {
        $this->dateMin = $dateMin;

        return $this;
    }

    public function getDateMax(): ?DateTimeImmutable
    {
        return $this->dateMax;
    }

    public function setDateMax(?DateTimeImmutable $dateMax): self
    {
        $this->dateMax = $dateMax;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function addTarget(Target $target): self
    {
        $target->setGoal($this);

        $this->targets->add($target);

        return $this;
    }

    public function clearTargets(): void
    {
        $this->targets->clear();
    }

    /**
     * @return Collection<int, Target>
     */
    public function getTargets(): Collection
    {
        return $this->targets;
    }

    public function removeTarget(Target $target): void
    {
        $this->targets->removeElement($target);
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}
