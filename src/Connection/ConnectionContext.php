<?php

declare(strict_types=1);

namespace Taladb\Connection;

use Taladb\Manager\CRUDManager;
use Taladb\Query\QueryExecutor;
use Taladb\Query\Result;

final class ConnectionContext
{
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly QueryExecutor $executor,
        private readonly CRUDManager $crud,
    ) {
    }

    public function query(
        string $sql,
        array $bindings = []
    ): Result {
        return $this->executor->execute(
            $sql,
            $bindings
        );
    }

    public function insert(
        string $table,
        array $data
    ): string {
        return $this->crud
            ->insert()
            ->execute(
                $table,
                $data
            );
    }

    public function update(
        string $table,
        array $data,
        array $where
    ): int {
        return $this->crud
            ->update()
            ->execute(
                $table,
                $data,
                $where
            );
    }

    public function delete(
        string $table,
        array $where
    ): int {
        return $this->crud
            ->delete()
            ->execute(
                $table,
                $where
            );
    }

    public function find(
        string $table,
        mixed $id
    ): ?array {
        return $this->crud
            ->find()
            ->execute(
                $table,
                $id
            );
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->connection;
    }
}