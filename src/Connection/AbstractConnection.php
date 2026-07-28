<?php

declare(strict_types=1);

namespace Taladb\Connection;

use PDO;
use PDOException;
use Taladb\Config\Config;
use Taladb\Exceptions\ConnectionException;

abstract class AbstractConnection implements ConnectionInterface
{
    protected ?PDO $pdo = null;

    public function __construct(
        protected readonly Config $config
    ) {
    }

    /**
     * Build the DSN for the database.
     */
    abstract protected function getDsn(): string;

    /**
     * Connect to the database.
     */
    public function connect(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        try {
            $this->pdo = new PDO(
                $this->getDsn(),
                $this->config->username,
                $this->config->password,
                $this->config->options
            );

            $this->configure($this->pdo);

            return $this->pdo;

        } catch (PDOException $e) {
            throw new ConnectionException(
                'Unable to connect to the database.',
                previous: $e
            );
        }
    }

    /**
     * Configure PDO after connection.
     */
    protected function configure(PDO $pdo): void
    {
        $pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        $pdo->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );

        $pdo->setAttribute(
            PDO::ATTR_EMULATE_PREPARES,
            false
        );
    }

    /**
     * Get the active PDO instance.
     */
    public function getPDO(): PDO
    {
        return $this->connect();
    }

    /**
     * Close the connection.
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }

    /**
     * Check if already connected.
     */
    public function isConnected(): bool
    {
        return $this->pdo instanceof PDO;
    }
}