<?php

declare(strict_types=1);

namespace Taladb\Builder;

use InvalidArgumentException;
use Taladb\Query\QueryExecutor;
final class QueryBuilder
{

    private const ALLOWED_OPERATORS = [
        '=', '!=', '<>', '<', '<=', '>', '>=',
        'LIKE', 'NOT LIKE', 'ILIKE',
    ];

    private const JOIN_TYPES = ['INNER', 'LEFT', 'RIGHT'];

    private array $columns = ['*'];
    private array $joins = [];
    private array $wheres = [];
    private array $groups = [];
    private array $havings = [];
    private array $orders = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private bool $distinct = false;
    private bool $randomOrder = false;

    private array $unions = [];

    public function __construct(
        private readonly QueryExecutor $executor,
        private readonly string $table,
    ) {
    }

    // -----------------------------------------------------------------
    // Terminal operations
    // -----------------------------------------------------------------

    public function get(): array
    {
        $query = $this->compile();

        return $this->executor
            ->execute($query['sql'], $query['bindings'])
            ->all();
    }

    public function first(): ?array
    {
        $query = (clone $this)->limit(1)->compile();

        return $this->executor
            ->execute($query['sql'], $query['bindings'])
            ->first();
    }

    public function exists(): bool
    {
        return $this->first() !== null;
    }

    public function value(string $column): mixed
    {
        $result = (clone $this)->select($column)->first();

        return $result[$column] ?? null;
    }

    public function pluck(string $column): array
    {
        $rows = (clone $this)->select($column)->get();

        return array_column($rows, $column);
    }

    public function insertGetId(array $data): string
    {
        return $this->executor->insert($this->table, $data);
    }

    public function chunk(int $size, callable $callback): void
    {
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

    public function paginate(int $perPage = 15, int $page = 1): array
    {
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
            'last_page' => (int) ceil($total / max($perPage, 1)),
        ];
    }


    private function compiled(): array
    {
        return $this->compile();
    }

    public function toSql(): string
    {
        return $this->compiled()['sql'];
    }

    public function getBindings(): array
    {
        return $this->compiled()['bindings'];
    }

    // -----------------------------------------------------------------
    // Select / columns
    // -----------------------------------------------------------------

    public function select(string ...$columns): self
    {
        if ($columns === []) {
            return $this;
        }

        $this->columns = $columns;

        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    // -----------------------------------------------------------------
    // Where clauses
    // -----------------------------------------------------------------

    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        return $this->addBasicWhere('AND', $column, $operator, $value);
    }

    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        return $this->addBasicWhere('OR', $column, $operator, $value);
    }

    public function whereIn(string $column, array $values): self
    {
        return $this->addInWhere('AND', $column, $values, negate: false);
    }

    public function orWhereIn(string $column, array $values): self
    {
        return $this->addInWhere('OR', $column, $values, negate: false);
    }

    public function whereNotIn(string $column, array $values): self
    {
        return $this->addInWhere('AND', $column, $values, negate: true);
    }

    public function orWhereNotIn(string $column, array $values): self
    {
        return $this->addInWhere('OR', $column, $values, negate: true);
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = ['type' => 'null', 'boolean' => 'AND', 'column' => $column];

        return $this;
    }
    public function inRandomOrder(): self
    {
        $this->randomOrder = true;

        return $this;
    }

    public function orWhereNull(string $column): self
    {
        $this->wheres[] = ['type' => 'null', 'boolean' => 'OR', 'column' => $column];

        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = ['type' => 'not_null', 'boolean' => 'AND', 'column' => $column];

        return $this;
    }

    public function orWhereNotNull(string $column): self
    {
        $this->wheres[] = ['type' => 'not_null', 'boolean' => 'OR', 'column' => $column];

        return $this;
    }

    public function whereBetween(string $column, array $values): self
    {
        return $this->addBetweenWhere('AND', $column, $values, negate: false);
    }

    public function whereNotBetween(string $column, array $values): self
    {
        return $this->addBetweenWhere('AND', $column, $values, negate: true);
    }

    private function addBasicWhere(string $boolean, string $column, mixed $operator, mixed $value): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $operator = strtoupper((string) $operator);

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported operator "%s".', $operator)
            );
        }

        $this->wheres[] = [
            'type' => 'basic',
            'boolean' => $boolean,
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    private function addInWhere(string $boolean, string $column, array $values, bool $negate): self
    {
        if ($values === []) {
            // An empty IN() is either always-false or always-true depending
            // on negation; short-circuit instead of emitting invalid SQL.
            $this->wheres[] = [
                'type' => $negate ? 'always_true' : 'always_false',
                'boolean' => $boolean,
            ];

            return $this;
        }

        $this->wheres[] = [
            'type' => $negate ? 'not_in' : 'in',
            'boolean' => $boolean,
            'column' => $column,
            'values' => array_values($values),
        ];

        return $this;
    }

    private function addBetweenWhere(string $boolean, string $column, array $values, bool $negate): self
    {
        if (count($values) !== 2) {
            throw new InvalidArgumentException(
                sprintf('%s expects exactly two values.', $negate ? 'whereNotBetween' : 'whereBetween')
            );
        }

        $this->wheres[] = [
            'type' => $negate ? 'not_between' : 'between',
            'boolean' => $boolean,
            'column' => $column,
            'values' => array_values($values),
        ];

        return $this;
    }

    // -----------------------------------------------------------------
    // Joins
    // -----------------------------------------------------------------

    public function join(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('INNER', $table, $first, $operator, $second);
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('LEFT', $table, $first, $operator, $second);
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->addJoin('RIGHT', $table, $first, $operator, $second);
    }

    private function addJoin(string $type, string $table, string $first, string $operator, string $second): self
    {
        $operator = strtoupper($operator);

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported join operator "%s".', $operator)
            );
        }

        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];

        return $this;
    }

    // -----------------------------------------------------------------
    // Grouping / having / ordering / limiting
    // -----------------------------------------------------------------

    public function groupBy(string ...$columns): self
    {
        array_push($this->groups, ...$columns);

        return $this;
    }

    public function having(string $column, mixed $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $operator = strtoupper((string) $operator);

        if (!in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported operator "%s".', $operator)
            );
        }

        $this->havings[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported sort direction "%s".', $direction)
            );
        }

        $this->orders[] = ['column' => $column, 'direction' => $direction];

        return $this;
    }

    public function latest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function limit(int $limit): self
    {
        $this->limit = max($limit, 0);

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max($offset, 0);

        return $this;
    }

    // -----------------------------------------------------------------
    // Aggregates
    // -----------------------------------------------------------------

    public function count(string $column = '*'): int
    {
        return (int) $this->aggregate('COUNT', $column);
    }

    public function max(string $column): mixed
    {
        return $this->aggregate('MAX', $column);
    }

    public function min(string $column): mixed
    {
        return $this->aggregate('MIN', $column);
    }

    public function avg(string $column): mixed
    {
        return $this->aggregate('AVG', $column);
    }

    public function sum(string $column): mixed
    {
        return $this->aggregate('SUM', $column);
    }

    private function aggregate(string $function, string $column = '*'): mixed
    {
        // Aggregates run against a clone so the caller's select() /
        // pagination state is never mutated by a stray count() call.
        $result = (clone $this)
            ->select(sprintf('%s(%s) AS aggregate', strtoupper($function), $column))
            ->first();

        return $result['aggregate'] ?? null;
    }

    // -----------------------------------------------------------------
    // Union
    // -----------------------------------------------------------------
    public function union(self $query): self
    {
        return $this->addUnion(all: false, query: $query);
    }

    public function unionAll(self $query): self
    {
        return $this->addUnion(all: true, query: $query);
    }

    private function addUnion(bool $all, self $query): self
    {
        $this->unions[] = [
            'all' => $all,
            'query' => clone $query,
        ];

        return $this;
    }


    // -----------------------------------------------------------------
    // Compilation
    // -----------------------------------------------------------------

    /**
     * @return array{sql: string, bindings: array}
     */
    private function compile(): array
    {
        $bindings = [];

        $sql = sprintf(
            'SELECT %s%s FROM %s',
            $this->distinct ? 'DISTINCT ' : '',
            implode(', ', $this->columns),
            $this->table
        );

        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres($bindings);
        $sql .= $this->compileGroups();
        $sql .= $this->compileHavings($bindings);
        $sql .= $this->compileUnions($bindings);
        $sql .= $this->compileOrders();
        $sql .= $this->compileLimitOffset();

        return ['sql' => $sql, 'bindings' => $bindings];
    }

    private function compileJoins(): string
    {
        $sql = '';

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

        return $sql;
    }

    private function compileWheres(array &$bindings): string
    {
        if ($this->wheres === []) {
            return '';
        }

        $conditions = [];

        foreach ($this->wheres as $index => $where) {
            $condition = match ($where['type']) {
                'basic' => $this->compileBasicWhere($where, $bindings),
                'in' => $this->compileInWhere($where, $bindings, negate: false),
                'not_in' => $this->compileInWhere($where, $bindings, negate: true),
                'null' => sprintf('%s IS NULL', $where['column']),
                'not_null' => sprintf('%s IS NOT NULL', $where['column']),
                'between' => $this->compileBetweenWhere($where, $bindings, negate: false),
                'not_between' => $this->compileBetweenWhere($where, $bindings, negate: true),
                'always_true' => '1 = 1',
                'always_false' => '1 = 0',
            };

            $conditions[] = $index === 0
                ? $condition
                : sprintf('%s %s', $where['boolean'], $condition);
        }

        return ' WHERE ' . implode(' ', $conditions);
    }

    private function compileBasicWhere(array $where, array &$bindings): string
    {
        $bindings[] = $where['value'];

        return sprintf('%s %s ?', $where['column'], $where['operator']);
    }

    private function compileInWhere(array $where, array &$bindings, bool $negate): string
    {
        $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));

        array_push($bindings, ...$where['values']);

        return sprintf(
            '%s %sIN (%s)',
            $where['column'],
            $negate ? 'NOT ' : '',
            $placeholders
        );
    }

    private function compileBetweenWhere(array $where, array &$bindings, bool $negate): string
    {
        $bindings[] = $where['values'][0];
        $bindings[] = $where['values'][1];

        return sprintf(
            '%s %sBETWEEN ? AND ?',
            $where['column'],
            $negate ? 'NOT ' : ''
        );
    }

    private function compileGroups(): string
    {
        if ($this->groups === []) {
            return '';
        }

        return ' GROUP BY ' . implode(', ', $this->groups);
    }

    private function compileHavings(array &$bindings): string
    {
        if ($this->havings === []) {
            return '';
        }

        $conditions = [];

        foreach ($this->havings as $having) {
            $conditions[] = sprintf('%s %s ?', $having['column'], $having['operator']);
            $bindings[] = $having['value'];
        }

        return ' HAVING ' . implode(' AND ', $conditions);
    }

    private function compileOrders(): string
    {
        if ($this->randomOrder) {
            return ' ORDER BY RAND()';
        }

        if ($this->orders === []) {
            return '';
        }

        $orderBy = array_map(
            static fn (array $order): string => sprintf('%s %s', $order['column'], $order['direction']),
            $this->orders
        );

        return ' ORDER BY ' . implode(', ', $orderBy);
    }

    private function compileLimitOffset(): string
    {
        $sql = '';

        if ($this->limit !== null) {
            $sql .= sprintf(' LIMIT %d', $this->limit);
        }

        if ($this->offset !== null) {
            $sql .= sprintf(' OFFSET %d', $this->offset);
        }

        return $sql;
    }

    private function compileUnions(array &$bindings): string
    {
        if ($this->unions === []) {
            return '';
        }

        $sql = '';

        foreach ($this->unions as $union) {
            /** @var self $unionQuery */
            $unionQuery = $union['query'];
            $compiled = $unionQuery->compile();

            $sql .= sprintf(
                ' UNION %s%s',
                $union['all'] ? 'ALL ' : '',
                $compiled['sql']
            );

            array_push($bindings, ...$compiled['bindings']);
        }

        return $sql;
    }


}