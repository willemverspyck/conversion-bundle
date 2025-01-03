<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;
use Stringable;

#[Doctrine\Entity]
#[Doctrine\Table(name: 'conversion_target')]
class Target implements Stringable, TimestampInterface
{
    use TimestampTrait;

    #[Doctrine\Column(name: 'id', type: Types::INTEGER, options: ['unsigned' => true])]
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'IDENTITY')]
    private ?int $id = null;

    #[Doctrine\ManyToOne(targetEntity: Goal::class, inversedBy: 'targets')]
    #[Doctrine\JoinColumn(name: 'goal_id', referencedColumnName: 'id', nullable: false)]
    private Goal $goal;

    #[Doctrine\Column(name: 'name', type: Types::STRING, length: 128, nullable: true)]
    private ?string $name = null;

    #[Doctrine\Column(name: 'variables', type: Types::JSON)]
    private array $variables;

    #[Doctrine\Column(name: 'value', type: Types::INTEGER)]
    private int $value;

    #[Doctrine\Column(name: 'remarks', type: Types::TEXT, nullable: true)]
    private ?string $remarks = null;

    #[Doctrine\Column(name: 'important', type: Types::BOOLEAN)]
    private bool $important;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGoal(): Goal
    {
        return $this->goal;
    }

    public function setGoal(Goal $goal): self
    {
        $this->goal = $goal;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): self
    {
        $this->variables = $variables;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getRemarks(): ?string
    {
        return $this->remarks;
    }

    public function setRemarks(?string $remarks): self
    {
        $this->remarks = $remarks;

        return $this;
    }

    public function isImportant(): bool
    {
        return $this->important;
    }

    public function setImportant(bool $important): self
    {
        $this->important = $important;

        return $this;
    }

    public function __toString(): string
    {
        $name = $this->getName();

        if (null === $name) {
            return sprintf('%d', $this->getId());
        }

        return $name;
    }
}
