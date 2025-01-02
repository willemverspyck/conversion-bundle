<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as Doctrine;

trait TimestampTrait
{
    #[Doctrine\Column(name: 'timestamp_created', type: Types::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $timestampCreated;

    #[Doctrine\Column(name: 'timestamp_updated', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?DateTimeImmutable $timestampUpdated = null;

    public function getTimestampCreated(): DateTimeImmutable
    {
        return $this->timestampCreated;
    }

    public function setTimestampCreated(DateTimeImmutable $timestampCreated): self
    {
        $this->timestampCreated = $timestampCreated;

        return $this;
    }

    public function getTimestampUpdated(): ?DateTimeImmutable
    {
        return $this->timestampUpdated;
    }

    public function setTimestampUpdated(DateTimeImmutable $timestampUpdated): self
    {
        $this->timestampUpdated = $timestampUpdated;

        return $this;
    }
}
