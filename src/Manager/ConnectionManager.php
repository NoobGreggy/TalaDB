<?php

declare(strict_types=1);

namespace Taladb\Manager;

use Taladb\Connection\ConnectionInterface;

final class ConnectionManager
{
    private array $connections = [];


    public function add(
        string $name,
        ConnectionInterface $connection
    ): void {

        $this->connections[$name] = $connection;
    }


    public function get(
        string $name = 'default'
    ): ConnectionInterface {

        if (!isset($this->connections[$name])) {

            throw new \RuntimeException(
                "Connection [$name] not found"
            );
        }


        return $this->connections[$name];
    }


    public function has(
        string $name
    ): bool {

        return isset(
            $this->connections[$name]
        );
    }


    public function remove(
        string $name
    ): void {

        unset(
            $this->connections[$name]
        );
    }
}