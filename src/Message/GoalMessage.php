<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Message;

final class GoalMessage implements GoalMessageInterface
{
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
