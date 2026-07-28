<?php

declare(strict_types=1);

namespace Taladb\Query;

use PDOException;
use Taladb\Connection\ConnectionInterface;
use Taladb\Exceptions\QueryException;

class QueryExecutor
{
    public function __construct(
        private ConnectionInterface $connection
    ) {
    }

    public function execute(
        string $sql,
        array $bindings = []
    ): Result {
        try {
            $pdo = $this->connection->getPDO();

            $statement = new Statement(
                $pdo->prepare($sql),
                $pdo
            );

            $statement
                ->bind($bindings)
                ->execute();

            return new Result($statement);

        } catch (PDOException $e) {
            throw new QueryException(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }
}