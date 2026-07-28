<?php

declare(strict_types=1);

namespace Taladb\Connection;

use PDO;

interface ConnectionInterface
{
    public function connect(): PDO;

    public function disconnect(): void;

    public function getPDO(): PDO;

    public function isConnected(): bool;
}