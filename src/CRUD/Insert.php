<?php

declare(strict_types=1);

namespace Taladb\CRUD;

use Taladb\Query\QueryExecutor;

class Insert
{
    public function __construct(
        private QueryExecutor $executor
    ) {
    }


    public function execute(
        string $table,
        array $data
    ): string {

        $columns = array_keys($data);


        $placeholders = array_fill(
            0,
            count($columns),
            '?'
        );


        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $table,
            implode(',', $columns),
            implode(',', $placeholders)
        );


        $result = $this->executor->execute(
            $sql,
            array_values($data)
        );


        return $result->lastInsertId();
    }
}