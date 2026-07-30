<?php

declare(strict_types=1);

namespace Taladb;

use RuntimeException;
use Taladb\Config\Config;
use Taladb\Connection\ConnectionFactory;
use Taladb\Connection\ConnectionInterface;
use Taladb\Query\QueryExecutor;
use Taladb\Query\Result;
use Taladb\Manager\CRUDManager;
use Taladb\Manager\ConnectionManager;
use Taladb\Connection\ConnectionContext;

use Taladb\Builder\QueryBuilder;

final class Taladb
{
    private ConnectionManager $connections;

    private array $queryExecutors = [];

    private array $crudManagers = [];


    public function __construct()
    {
        $this->connections = new ConnectionManager();
    }


    public function connect(
        Config $config,
        string $name = 'default'
    ): self {

        $factory = new ConnectionFactory();

        $connection = $factory->make($config);

        $connection->connect();


        $this->connections->add(
            $name,
            $connection
        );


        $executor = new QueryExecutor(
            $connection
        );


        $this->queryExecutors[$name] = $executor;


        $this->crudManagers[$name] = new CRUDManager(
            $executor
        );


        return $this;
    }


    private function executor(
        string $name = 'default'
    ): QueryExecutor {

        if (!isset($this->queryExecutors[$name])) {

            throw new RuntimeException(
                "Query executor [$name] not found"
            );
        }


        return $this->queryExecutors[$name];
    }


    private function crud(
        string $name = 'default'
    ): CRUDManager {

        if (!isset($this->crudManagers[$name])) {

            throw new RuntimeException(
                "CRUD manager [$name] not found"
            );
        }


        return $this->crudManagers[$name];
    }


    public function connection(
        string $name = 'default'
    ): ConnectionContext {

        return new ConnectionContext(
            $this->connections->get($name),
            $this->executor($name),
            $this->crud($name)
        );
    }


    public function query(
        string $sql,
        array $bindings = [],
        string $connection = 'default'
    ): Result {

        return $this->executor($connection)
            ->execute(
                $sql,
                $bindings
            );
    }


    public function insert(
        string $table,
        array $data,
        string $connection = 'default'
    ): string {

        return $this->crud($connection)
            ->insert()
            ->execute(
                $table,
                $data
            );
    }


    public function update(
        string $table,
        array $data,
        array $where,
        string $connection = 'default'
    ): int {

        return $this->crud($connection)
            ->update()
            ->execute(
                $table,
                $data,
                $where
            );
    }


    public function delete(
        string $table,
        array $where,
        string $connection = 'default'
    ): int {

        return $this->crud($connection)
            ->delete()
            ->execute(
                $table,
                $where
            );
    }


    public function find(
        string $table,
        mixed $id,
        string $connection = 'default'
    ): ?array {

        return $this->crud($connection)
            ->find()
            ->execute(
                $table,
                $id
            );
    }

    public function table(
        string $table,
        string $connection = 'default'
    ): QueryBuilder {

        return new QueryBuilder(
            $this->executor($connection),
            $table
        );
    }
}