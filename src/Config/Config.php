<?php

namespace Taladb\Config;

final readonly class Config
{
    public function __construct(
        public string $driver,
        public string $host = '',
        public int    $port = 0,
        public string $database = '',
        public string $username = '',
        public string $password = '',
        public array  $options = [],
    ) {}
}