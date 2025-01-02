<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Entity;

use DateTimeImmutable;

interface TimestampInterface
{
    public function getTimestampCreated(): DateTimeImmutable;

    public function setTimestampCreated(DateTimeImmutable $timestampCreated): self;

    public function getTimestampUpdated(): ?DateTimeImmutable;

    public function setTimestampUpdated(DateTimeImmutable $timestampUpdated): self;
}
