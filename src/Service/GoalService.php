<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Service;

use DateTimeImmutable;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use Spyck\ConversionBundle\Entity\Goal;
use Spyck\ConversionBundle\Entity\Target;
use Spyck\ConversionBundle\Goal\GoalInterface;
use Spyck\ConversionBundle\Message\GoalMessage;
use Spyck\ConversionBundle\Repository\GoalRepository;
use Spyck\ConversionBundle\Utility\ArrayUtility;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class GoalService
{
    private const array FIELDS = ['date', 'status', 'timestamp_created', 'timestamp_updated'];

    public function __construct(private DatabaseService $databaseService, private GoalRepository $goalRepository, private MessageBusInterface $messageBus, #[AutowireLocator(services: 'spyck.conversion.goal', defaultIndexMethod: 'getName')] private ServiceLocator $serviceLocator)
    {
    }

    /**
     * @throws Exception
     */
    public function getGoal(string $name): GoalInterface
    {
        return $this->serviceLocator->get($name);
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function executeGoal(Goal $goal): void
    {
        $goalInstance = $this->getGoal($goal->getAdapter());
        $goalInstance->setGoal($goal);

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
