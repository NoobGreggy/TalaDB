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
//            driver: 'pgsql',
//            host: 'localhost',
//            port: 5433, // Default PostgreSQL port
//            database: 'talaDB',
//            username: 'postgres',
//            password: 'T3@m@1j2019'
        );


        $this->db = new Taladb();

        $this->db->connect($config);

        // Clean the table before every test
        $this->db->query("DELETE FROM users");
        $this->db->query("ALTER TABLE users AUTO_INCREMENT = 1");
    }

    protected function seedUsers(): void
    {
        $this->db->insert('users', [
            'name' => 'John',
            'email' => 'john@test.com',
        ]);

        $this->db->insert('users', [
            'name' => 'Jane',
            'email' => 'jane@test.com',
        ]);

        $this->db->insert('users', [
            'name' => 'Gregg',
            'email' => 'gregg@test.com',
        ]);
    }
}