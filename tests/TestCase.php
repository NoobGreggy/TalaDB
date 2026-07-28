<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Taladb\Config\Config;
use Taladb\Taladb;

abstract class TestCase extends PHPUnitTestCase
{
    protected Taladb $db;


    protected function setUp(): void
    {
        $config = new Config(
            driver: 'mysql',
            host: 'localhost',
            port: 3308,
            database: 'talaDB',
            username: 'root',
            password: 'T3@m@1j2019'
        );


        $this->db = new Taladb();

        $this->db->connect($config);
    }
}