<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\Service\Attribute\Required;

trait DoctrineTrait
{
    private readonly EntityManagerInterface $entityManager;

    #[Required]
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getData(): iterable
    {
        return $this->getDataFromDoctrine()
            ->getQuery()
            ->getArrayResult();
    }

    protected function getQueryBuilder(bool $autowire = true): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        if (false === $autowire) {
            return $queryBuilder;
        }

        return $queryBuilder;
    }
}
