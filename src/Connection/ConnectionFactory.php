<?php

declare(strict_types=1);

namespace Taladb\Connection;

use Taladb\Config\Config;
use Taladb\Connection\Connections\MySQLConnection;
use Taladb\Connection\Connections\PostgreSQLConnection;
use Taladb\Connection\Connections\SQLiteConnection;
use Taladb\Connection\Connections\MariaDBConnection;
use Taladb\Connection\Connections\SQLServerConnection;
use Taladb\Exceptions\ConnectionException;

final class ConnectionFactory
{
    public function make(Config $config): MySQLConnection|PostgreSQLConnection|SQLiteConnection
    {
        return match (strtolower($config->driver)) {

            'mysql' => new MySQLConnection($config),

            'mariadb' => new MariaDBConnection($config),

            'pgsql',
            'postgres',
            'postgresql' => new PostgreSQLConnection($config),

            'sqlite' => new SQLiteConnection($config),

            'sqlsrv',
            'sqlserver',
            'mssql' => new SQLServerConnection($config),


            default => throw new ConnectionException(
                "Unsupported driver [{$config->driver}]"
            ),
        };
    }
}