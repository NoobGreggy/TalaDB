<?php

declare(strict_types=1);

namespace Taladb\Connection\Connections;

use Taladb\Connection\AbstractConnection;

final class SQLiteConnection extends AbstractConnection
{
    protected function getDsn(): string
    {
        return 'sqlite:' . $this->config->database;
    }
}