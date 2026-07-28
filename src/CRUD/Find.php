<?php
declare(strict_types=1);

namespace Taladb\CRUD;

use Taladb\Query\QueryExecutor;

class Find
{
    public function __construct(
        private QueryExecutor $executor
    )
    {
    }


    public function execute(
        string $table,
        mixed  $id
    ): ?array
    {

        $sql = sprintf(
            "SELECT * FROM %s WHERE id = ? LIMIT 1",
            $table
        );


        $result = $this->executor->execute(
            $sql,
            [
                $id
            ]
        );


        return $result->first();
    }
}