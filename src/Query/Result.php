<?php

declare(strict_types=1);

namespace Taladb\Query;

use PDO;

class Result
{
    public function __construct(
        private Statement $statement
    ) {
    }


    public function all(): array
    {
        return $this
            ->statement
            ->getPDOStatement()
            ->fetchAll(PDO::FETCH_ASSOC);
    }


    public function first(): ?array
    {
        $row = $this
            ->statement
            ->getPDOStatement()
            ->fetch(PDO::FETCH_ASSOC);

        return $row === false
            ? null
            : $row;
    }


    public function count(): int
    {
        return count(
            $this->all()
        );
    }


    public function column(): array
    {
        return $this
            ->statement
            ->getPDOStatement()
            ->fetchAll(PDO::FETCH_COLUMN);
    }


    public function json(): string
    {
        return json_encode(
            $this->all(),
            JSON_THROW_ON_ERROR
        );
    }

    public function affected(): int
    {
        return $this->statement->affectedRows();
    }

    public function lastInsertId(): string
    {
        return $this->statement->lastInsertId();
    }
}