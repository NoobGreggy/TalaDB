<?php

declare(strict_types=1);

namespace Taladb\Connection\Connections;

use Taladb\Connection\AbstractConnection;

final class SQLServerConnection extends AbstractConnection
{
    protected function getDsn(): string
    {
        return sprintf(
            'sqlsrv:Server=%s,%d;Database=%s',
            $this->config->host,
            $this->config->port ?: 1433,
            $this->config->database
        );
    }
}