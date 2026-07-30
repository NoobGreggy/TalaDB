<?php

declare(strict_types=1);

namespace Taladb\Builder;

use Taladb\Query\QueryExecutor;

final class QueryBuilder
{
    public function __construct(
        private readonly QueryExecutor $executor,
        private readonly string $table,
    ) {
    }

    public function get(): array
    {
        return $this->executor
            ->execute(
                sprintf(
                    'SELECT * FROM %s',
                    $this->table
                )
            )
            ->all();
    }

    public function first(): ?array
    {
        return $this->executor
            ->execute(
                sprintf(
                    'SELECT * FROM %s LIMIT 1',
                    $this->table
                )
            )
            ->first();
    }
}