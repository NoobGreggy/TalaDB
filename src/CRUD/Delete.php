<?php

declare(strict_types=1);

namespace Taladb\CRUD;

use Taladb\Query\QueryExecutor;

class Delete
{
    public function __construct(
        private QueryExecutor $executor
    ) {
    }


    public function execute(
        string $table,
        array $where
    ): int {

        $conditions = [];

        $bindings = [];


        foreach ($where as $column => $value) {

            $conditions[] = "{$column} = ?";

            $bindings[] = $value;
        }


        $sql = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            implode(' AND ', $conditions)
        );


        $result = $this->executor->execute(
            $sql,
            $bindings
        );


        return $result->affected();
    }
}