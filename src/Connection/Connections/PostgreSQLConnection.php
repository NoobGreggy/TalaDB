<?php

declare(strict_types=1);

namespace Taladb\Connection\Connections;

use Taladb\Connection\AbstractConnection;

final class PostgreSQLConnection extends AbstractConnection
{
    protected function getDsn(): string
    {
        return sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $this->config->host,
            $this->config->port ?: 5432,
            $this->config->database
        );
    }
}