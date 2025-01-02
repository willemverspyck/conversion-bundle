<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Parameter;

abstract class AbstractParameter implements ParameterInterface
{
    private ?string $data = null;

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }
}
