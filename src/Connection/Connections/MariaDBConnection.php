<?php

declare(strict_types=1);

namespace Taladb\Connection\Connections;

use Taladb\Connection\AbstractConnection;

final class MariaDBConnection extends AbstractConnection
{
    protected function getDsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $this->config->host,
            $this->config->port ?: 3306,
            $this->config->database
        );
    }
}