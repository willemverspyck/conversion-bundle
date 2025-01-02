<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Repository;

use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use Spyck\ConversionBundle\Entity\Goal;

class GoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Goal::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function getGoalById(int $id): ?Goal
    {
        return $this->createQueryBuilder('goal')
            ->addSelect('target')
            ->innerJoin('goal.targets', 'target')
            ->where('goal.id = :id')
            ->andWhere('goal.active = TRUE')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get all goals.
     *
     * @return array<int, Goal>
     */
    public function getGoalsByType(string $type): array
    {
        return $this->createQueryBuilder('goal')
            ->addSelect('target')
            ->innerJoin('goal.targets', 'target')
            ->where('goal.type = :type')
            ->andWhere('goal.active = TRUE')
            ->setParameter('type', $type)
            ->getQuery()
            ->getResult();
    }

    /**
     * Patch goal.
     */
    public function patchGoal(Goal $goal, array $fields, ?DateTimeImmutable $dateMin = null, ?DateTimeImmutable $dateMax = null): void
    {
        if (in_array('dateMin', $fields, true)) {
            $goal->setDateMin($dateMin);
        }

        if (in_array('dateMax', $fields, true)) {
            $goal->setDateMax($dateMax);
        }

        $this->getEntityManager()->persist($goal);
        $this->getEntityManager()->flush();
    }
}
