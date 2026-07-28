<?php

declare(strict_types=1);

namespace Taladb\CRUD;

use Taladb\Query\QueryExecutor;

class Update
{
    public function __construct(
        private QueryExecutor $executor
    ) {
    }


    public function execute(
        string $table,
        array $data,
        array $where
    ): int {

        $set = [];

        $bindings = [];


        foreach ($data as $column => $value) {

            $set[] = "{$column} = ?";

            $bindings[] = $value;
        }


        $conditions = [];


        foreach ($where as $column => $value) {

            $conditions[] = "{$column} = ?";

            $bindings[] = $value;
        }


        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s",
            $table,
            implode(', ', $set),
            implode(' AND ', $conditions)
        );


        $result = $this->executor->execute(
            $sql,
            $bindings
        );


        return $result->affected();
    }
}