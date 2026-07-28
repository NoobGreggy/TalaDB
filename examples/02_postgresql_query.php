<?php


declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';


use Taladb\Config\Config;
use Taladb\Taladb;


$config = new Config(
    driver: 'pgsql',
    host: 'localhost',
    port: 5433, // Default PostgreSQL port
    database: 'talaDB',
    username: 'postgres',
    password: 'T3@m@1j2019'
);


$db = new Taladb();


try {

    $db->connect($config);


    echo "Database connected\n";


    /*
     |--------------------------------------------------------------------------
     | Test 1: Simple SELECT
     |--------------------------------------------------------------------------
     */

    $result = $db->query(
        "SELECT * FROM users"
    );


    print_r(
        $result->all()
    );


    /*
     |--------------------------------------------------------------------------
     | Test 2: Binding parameters
     |--------------------------------------------------------------------------
     */

    $result = $db->query(
        "SELECT * FROM users WHERE id = ?",
        [1]
    );


    print_r(
        $result->first()
    );


} catch (Throwable $e) {

    echo "ERROR:\n";

    echo $e->getMessage();

}