<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Service;

use Countable;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use IteratorAggregate;
use Spyck\ConversionBundle\Entity\Goal;
use Spyck\ConversionBundle\Entity\Target;
use Spyck\ConversionBundle\Goal\GoalInterface;
use Spyck\ConversionBundle\Message\GoalMessage;
use Spyck\ConversionBundle\Repository\GoalRepository;
use Spyck\ConversionBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class GoalService
{
    private const array FIELDS = ['date', 'status', 'timestamp_created', 'timestamp_updated'];

    /**
     * @param Countable&IteratorAggregate $goals
     */
    public function __construct(private DatabaseService $databaseService, private GoalRepository $goalRepository, private MessageBusInterface $messageBus, #[AutowireIterator(tag: 'spyck.conversion.goal')] private iterable $goals)
    {
    }

    /**
     * @throws Exception
     */
    public function getGoal(string $name): GoalInterface
    {
        foreach ($this->goals->getIterator() as $goal) {
            if (get_class($goal) === $name) {
                return $goal;
            }
        }

        throw new Exception(sprintf('Goal "%s" not found', $name));
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function executeGoal(Goal $goal): void
    {
        $date = $this->getDate($goal);

        $goalInstance = $this->getGoal($goal->getAdapter());
        $goalInstance->setDate($date);
        $goalInstance->setTargets($goal->getTargets());

        $data = $goalInstance->getData();
        $entity = $goalInstance->getEntity();

        foreach ($goal->getTargets() as $target) {
            $table = $this->databaseService->getTable($entity);

            $this->databaseService->putStatus($table, $target);

            foreach ($data as $row) {
                $field = sprintf('target_%d', $target->getId());

                ArrayUtility::hasKeysInArrayWithException([$field], $row);

                $date = $row[$field];

                if (null !== $date) {
                    $fields = array_filter($this->databaseService->getFields($entity), function (string $field): bool {
                        return false === in_array($field, self::FIELDS, true);
                    });

                    $fieldsData = [];

                    foreach ($fields as $field) {
                        if (array_key_exists($field, $row)) {
                            $fieldsData[$field] = $row[$field];
                        }
                    }

                    $this->executeQuery($target, $table, $fieldsData, $date);
                }
            }

            if (null === $goalInstance->getInterval()) {
                $this->databaseService->deleteStatus($table, $target);
            }
        }

        $dateMax = new DateTimeImmutable();

        $this->goalRepository->patchGoal(goal: $goal, fields: ['dateMax'], dateMax: $dateMax);
    }

    /**
     * @throws ExceptionInterface
     */
    public function executeGoalAsMessage(Goal $goal): void
    {
        $goalMessage = new GoalMessage();
        $goalMessage->setId($goal->getId());

        $this->messageBus->dispatch($goalMessage);
    }

    /**
     * @throws DateMalformedStringException
     */
    private function getDate(Goal $goal): DateTimeInterface
    {
        if (null === $goal->getDateMax()) {
            return $goal->getDateMin();
        }

        return $goal->getDateMax();
    }

    /**
     * @throws DBALException
     */
    private function executeQuery(Target $target, string $table, array $fields, string $date): void
    {
        $fields1 = array_map(function (string $field): string {
            return sprintf('`%s`', $field);
        }, array_keys($fields));

        $fields2 = array_map(function (string $field): string {
            return sprintf(':%s', $field);
        }, array_keys($fields));

        $sql = sprintf('INSERT INTO `%s` (`target_id`, %s, `date`, `timestamp_created`, `status`) VALUES (:target_id, %s, :date, CURRENT_TIMESTAMP(), 0) ON DUPLICATE KEY UPDATE `date` = IF(`date` > :date, :date, `date`), `timestamp_updated` = CURRENT_TIMESTAMP(), `status` = 0', $table, implode(', ', $fields1), implode(', ', $fields2));

        $fields['target_id'] = $target->getId();
        $fields['date'] = $date;

        $this->databaseService->executeQuery($sql, $fields);
    }
}
