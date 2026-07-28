<?php

declare(strict_types=1);

namespace Taladb\Query;

use PDO;
use PDOStatement;

class Statement
{
    public function __construct(
        private PDOStatement $statement,
        private PDO $pdo
    ) {
    }


    public function bind(array $bindings): self
    {
        foreach ($bindings as $key => $value) {

            $parameter = is_string($key)
                ? $key
                : $key + 1;

            $this->statement->bindValue(
                $parameter,
                $value
            );
        }

        return $this;
    }


    public function execute(): self
    {
        $this->statement->execute();

        return $this;
    }


    public function affectedRows(): int
    {
        return $this->statement->rowCount();
    }


    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }


    public function getPDOStatement(): PDOStatement
    {
        return $this->statement;
    }
}