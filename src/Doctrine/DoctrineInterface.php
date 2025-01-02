<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Spyck\ConversionBundle\Goal\GoalInterface;

interface DoctrineInterface extends GoalInterface
{
    /**
     * Get data from Doctrine with QueryBuilder.
     */
    public function getDataFromDoctrine(): QueryBuilder;
}
