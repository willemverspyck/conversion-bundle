<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Parameter;

interface ParameterInterface
{
    public function getData(): ?string;

    public function setData(?string $data): void;

    public function getField(): string;

    public function getName(): string;
}
