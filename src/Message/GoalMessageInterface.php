<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Message;

interface GoalMessageInterface
{
    public function getId(): int;

    public function setId(int $id): void;
}
