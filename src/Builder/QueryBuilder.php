<?php

declare(strict_types=1);

namespace Taladb\Builder;

use Taladb\Query\QueryExecutor;

final class QueryBuilder
{
    private array $wheres = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $columns = ['*'];
    private array $joins = [];

    private array $groups = [];

    private bool $distinct = false;

    private array $havings = [];


    public function __construct(
        private readonly QueryExecutor $executor,
        private readonly string $table,
    ) {
    }

    public function get(): array
    {
        $query = $this->compile();

        return $this->executor
            ->execute(
                $query['sql'],
                $query['bindings']
            )
            ->all();
    }

    public function first(): ?array
    {
        $this->limit(1);

        $query = $this->compile();

        return $this->executor
            ->execute(
                $query['sql'],
                $query['bindings']
            )
            ->first();
    }

    public function where(
        string $column,
        mixed $operator,
        mixed $value = null
    ): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'AND',
            'column' => $column,
            'operator' => strtoupper((string) $operator),
            'value' => $value,
        ];

        return $this;
    }

    private function compile(): array
    {
        $sql = sprintf(
            'SELECT %s%s FROM %s',
            $this->distinct ? 'DISTINCT ' : '',
            implode(', ', $this->columns),
            $this->table
        );

        foreach ($this->joins as $join) {

            $sql .= sprintf(
                ' %s JOIN %s ON %s %s %s',
                $join['type'],
                $join['table'],
                $join['first'],
                $join['operator'],
                $join['second']
            );
        }


        $bindings = [];

        if ($this->wheres !== []) {

            $conditions = [];

            foreach ($this->wheres as $index => $where) {

                switch ($where['type']) {

                    case 'basic':

                        $condition = sprintf(
                            '%s %s ?',
                            $where['column'],
                            $where['operator']
                        );

                        $bindings[] = $where['value'];

                        break;

                    case 'in':

                        $placeholders = implode(
                            ', ',
                            array_fill(
                                0,
                                count($where['values']),
                                '?'
                            )
                        );

                        $condition = sprintf(
                            '%s IN (%s)',
                            $where['column'],
                            $placeholders
                        );

                        foreach ($where['values'] as $value) {
                            $bindings[] = $value;
                        }

                        break;

                    case 'null':

                        $condition = sprintf(
                            '%s IS NULL',
                            $where['column']
                        );

                        break;

                    case 'not_null':

                        $condition = sprintf(
                            '%s IS NOT NULL',
                            $where['column']
                        );

                        break;

                    case 'between':

                        $condition = sprintf(
                            '%s BETWEEN ? AND ?',
                            $where['column']
                        );

                        $bindings[] = $where['values'][0];
                        $bindings[] = $where['values'][1];

                        break;
                     case 'not_between':

                        $condition = sprintf(
                            '%s NOT BETWEEN ? AND ?',
                            $where['column']
                        );

                        $bindings[] = $where['values'][0];
                        $bindings[] = $where['values'][1];

                        break;
                }

                if ($index === 0) {
                    $conditions[] = $condition;
                } else {
                    $conditions[] = sprintf(
                        '%s %s',
                        $where['boolean'],
                        $condition
                    );
                }
            }

            $sql .= ' WHERE ' . implode(' ', $conditions);

        }


        if ($this->groups !== []) {

            $sql .= ' GROUP BY ' . implode(
                    ', ',
                    $this->groups
                );
        }

        if ($this->havings !== []) {

            $conditions = [];

            foreach ($this->havings as $having) {

                $conditions[] = sprintf(
                    '%s %s ?',
                    $having['column'],
                    $having['operator']
                );

                $bindings[] = $having['value'];
            }

            $sql .= ' HAVING ' . implode(
                    ' AND ',
                    $conditions
                );
        }

        if ($this->orders !== []) {

            $orderBy = [];

            foreach ($this->orders as $order) {

                $orderBy[] = sprintf(
                    '%s %s',
                    $order['column'],
                    $order['direction']
                );
            }

            $sql .= ' ORDER BY ' . implode(', ', $orderBy);
        }

        if ($this->limit !== null) {
            $sql .= sprintf(
                ' LIMIT %d',
                $this->limit
            );
        }

        if ($this->offset !== null) {

            $sql .= sprintf(
                ' OFFSET %d',
                $this->offset
            );
        }

        return [
            'sql' => $sql,
            'bindings' => $bindings,
        ];
    }

    public function orderBy(
        string $column,
        string $direction = 'ASC'
    ): self {

        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'ASC';
        }

        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    public function limit(
        int $limit
    ): self {

        if ($limit < 0) {
            $limit = 0;
        }

        $this->limit = $limit;

        return $this;
    }

    public function offset(
        int $offset
    ): self {

        if ($offset < 0) {
            $offset = 0;
        }

        $this->offset = $offset;

        return $this;
    }

    public function select(
        string ...$columns
    ): self {

        if ($columns === []) {
            return $this;
        }

        $this->columns = $columns;

        return $this;
    }

    public function orWhere(
        string $column,
        mixed $operator,
        mixed $value = null
    ): self {

        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => 'OR',   // <-- FIX THIS
            'column' => $column,
            'operator' => strtoupper((string) $operator),
            'value' => $value,
        ];

        return $this;
    }

    public function whereIn(
        string $column,
        array $values
    ): self {

        if ($values === []) {
            return $this;
        }

        $this->wheres[] = [
            'type' => 'in',
            'boolean' => 'AND',
            'column' => $column,
            'values' => $values,
        ];

        return $this;
    }

    public function whereNull(
        string $column
    ): self {

        $this->wheres[] = [
            'type' => 'null',
            'boolean' => 'AND',
            'column' => $column,
        ];

        return $this;
    }

    public function whereNotNull(
        string $column
    ): self {

        $this->wheres[] = [
            'type' => 'not_null',
            'boolean' => 'AND',
            'column' => $column,
        ];

        return $this;
    }

    public function whereBetween(
        string $column,
        array $values
    ): self {

        if (count($values) !== 2) {
            throw new \InvalidArgumentException(
                'whereBetween expects exactly two values.'
            );
        }

        $this->wheres[] = [
            'type' => 'between',
            'boolean' => 'AND',
            'column' => $column,
            'values' => array_values($values),
        ];

        return $this;
    }

    public function whereNotBetween(
        string $column,
        array $values
    ): self {

        if (count($values) !== 2) {
            throw new \InvalidArgumentException(
                'whereNotBetween expects exactly two values.'
            );
        }

        $this->wheres[] = [
            'type' => 'not_between',
            'boolean' => 'AND',
            'column' => $column,
            'values' => array_values($values),
        ];

        return $this;
    }

    public function join(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {
        return $this->addJoin(
            'INNER',
            $table,
            $first,
            $operator,
            $second
        );
    }

    public function leftJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->addJoin(
            'LEFT',
            $table,
            $first,
            $operator,
            $second
        );
    }


    public function rightJoin(
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        return $this->addJoin(
            'RIGHT',
            $table,
            $first,
            $operator,
            $second
        );
    }


    private function addJoin(
        string $type,
        string $table,
        string $first,
        string $operator,
        string $second
    ): self {

        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];

        return $this;
    }

    public function groupBy(
        string ...$columns
    ): self {

        foreach ($columns as $column) {
            $this->groups[] = $column;
        }

        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    public function having(
        string $column,
        mixed $operator,
        mixed $value = null
    ): self {

        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'column' => $column,
            'operator' => strtoupper((string) $operator),
            'value' => $value,
        ];

        return $this;
    }

    public function count(
        string $column = '*'
    ): int {

        return (int) $this->aggregate(
            'COUNT',
            $column
        );
    }

    public function max(
        string $column
    ): mixed {

        return $this->aggregate(
            'MAX',
            $column
        );
    }

    public function min(
        string $column
    ): mixed {

        return $this->aggregate(
            'MIN',
            $column
        );
    }

    public function avg(
        string $column
    ): mixed {

        return $this->aggregate(
            'AVG',
            $column
        );
    }

    public function sum(
        string $column
    ): mixed {

        return $this->aggregate(
            'SUM',
            $column
        );
    }


    private function aggregate(
        string $function,
        string $column = '*'
    ): mixed
    {
        $previousColumns = $this->columns;

        $this->columns = [
            sprintf(
                '%s(%s) AS aggregate',
                strtoupper($function),
                $column
            )
        ];

        $result = $this->first();

        $this->columns = $previousColumns;

        return $result['aggregate'] ?? null;
    }

    public function insertGetId(
        array $data
    ): string {

        return $this->executor
            ->insert(
                $this->table,
                $data
            );
    }

    public function chunk(
        int $size,
        callable $callback
    ): void {

        $page = 1;

        while (true) {

            $results = (clone $this)
                ->limit($size)
                ->offset(($page - 1) * $size)
                ->get();

            if ($results === []) {
                break;
            }

            $callback($results);

            if (count($results) < $size) {
                break;
            }

            $page++;
        }
    }


    public function paginate(
        int $perPage = 15,
        int $page = 1
    ): array {

        $total = (clone $this)->count();

        $data = (clone $this)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil(
                $total / $perPage
            ),
        ];
    }









}