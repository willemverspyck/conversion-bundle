<?php

declare(strict_types=1);

namespace Spyck\ConversionBundle\Service;

use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Spyck\ConversionBundle\Entity\Target;

class DatabaseService
{
    private const int TRANSACTION_MAX_SIZE = 2500;

    private array $transaction = [];

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @throws Exception
     */
    public function getTable(string $entity): string
    {
        return $this->entityManager->getClassMetadata($entity)->getTableName();
    }

    /**
     * @return array<string, int>
     *
     * @throws Exception
     */
    public function getFields(string $entity): array
    {
        $classMetadata = $this->entityManager->getClassMetadata($entity);

        $fields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $fields[] = $classMetadata->getFieldMapping($fieldName)->columnName;
        }

        return $fields;
    }

    /**
     * Insert existing data.
     *
     * @throws DBALException
     */
    public function executeQuery(string $sql, array $fields = []): Result
    {
        $statement = $this->entityManager->getConnection()->prepare($sql);

        foreach ($fields as $fieldName => $fieldValue) {
            $statement->bindValue($fieldName, $fieldValue);
        }

        return $statement->executeQuery();
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function putStatus(string $table, Target $target): void
    {
        $status = $this->checkStatus($table, $target);

        if (false === $status) {
            return;
        }

        $sql = sprintf('UPDATE `%s` SET `status` = 1 WHERE `target_id` = :targetId AND `status` = 0', $table);

        $fields = [
            'targetId' => $target->getId(),
        ];

        $this->entityManager->getConnection()->executeStatement($sql, $fields);
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function deleteStatus(string $table, Target $target): void
    {
        $status = $this->checkStatus($table, $target);

        if (false === $status) {
            return;
        }

        $fields = [
            'targetId' => $target->getId(),
        ];

        $sql = sprintf('DELETE FROM `%s` WHERE `target_id` = :targetId AND `status` = 1', $table);

        $this->entityManager->getConnection()->executeStatement($sql, $fields);
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    private function checkStatus(string $table, Target $target): bool
    {
        $sql = sprintf('SELECT COUNT(*) FROM `%s` WHERE `target_id` = :targetId AND `status` = 0', $table);

        $fields = [
            'targetId' => $target->getId(),
        ];

        $data = $this->entityManager->getConnection()->executeQuery($sql, $fields)->fetchOne();

        if (false === $data) {
            throw new Exception('Error on condition');
        }

        return '0' !== $data;
    }

    /**
     * Create query with "INSERT INTO ... ON DUPLICATE KEY UPDATE ..." and add to transaction.
     *
     * @throws Exception
     */
    public function insertOnDuplicateKeyUpdate(string $table, array $insertFields, array $updateFields): void
    {
        $insertFieldsName = [];
        $insertFieldsParameter = [];

        foreach (array_keys($insertFields) as $name) {
            $insertFieldsName[] = sprintf('`%s`', $name);
            $insertFieldsParameter[] = sprintf(':insert_%s', $name);
        }

        $updateFieldsQuery = [];

        foreach (array_keys($updateFields) as $name) {
            $updateFieldsQuery[] = sprintf('`%s` = :update_%s', $name, $name);
        }

        $sql = sprintf('INSERT IGNORE INTO `%s` (%s) VALUES (%s)', $table, implode(', ', $insertFieldsName), implode(', ', $insertFieldsParameter));

        if (count($updateFieldsQuery) > 0) {
            $sql = sprintf('%s ON DUPLICATE KEY UPDATE %s', $sql, implode(', ', $updateFieldsQuery));
        }

        $parameters = [];

        foreach ($insertFields as $name => $value) {
            $parameters[sprintf('insert_%s', $name)] = $value;
        }

        foreach ($updateFields as $name => $value) {
            $parameters[sprintf('update_%s', $name)] = $value;
        }

        $this->transactionQuery($sql, $parameters);
    }

    /**
     * Add query to transaction queue.
     *
     * @throws Exception
     */
    public function transactionQuery(string $sql, array $parameters = [], ?callable $callback = null): void
    {
        $this->transaction[] = ['sql' => $sql, 'parameters' => $parameters];

        if (count($this->transaction) >= self::TRANSACTION_MAX_SIZE) {
            $this->transactionCommit();

            if (null !== $callback) {
                $callback();
            }
        }
    }

    /**
     * Commit the transaction queue.
     *
     * @throws DBALException
     */
    public function transactionCommit(): void
    {
        if (0 === count($this->transaction)) {
            return;
        }

        $connection = $this->entityManager->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($this->transaction as $transaction) {
                $connection->executeStatement($transaction['sql'], $transaction['parameters']);
            }

            $this->entityManager->getConnection()->commit();
        } catch (DriverException $driverException) {
            $connection->rollBack();

            $query = $driverException->getQuery();

            $this->logger->error($driverException->getMessage(), [
                'sql' => $query->getSQL(),
                'parameters' => $query->getParams(),
            ]);

            throw new Exception($driverException->getMessage());
        } catch (Exception $exception) {
            $connection->rollBack();

            throw new Exception($exception->getMessage());
        }

        $this->transaction = [];
    }
}
