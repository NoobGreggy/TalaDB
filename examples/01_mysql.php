<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PDO;
use Taladb\Config\Config;
use Taladb\Taladb;

$db = new Taladb();

try {

    $db->connect(new Config(
        driver: 'mysql',
        host: 'localhost',
        port: 3307,
        database: 'talaDB',
        username: 'root',
        password: 'T3@m@1j2019'
    ));

    $pdo = $db->connection()->getPDO();

    echo "===============================\n";
    echo "      Taladb Connection\n";
    echo "===============================\n";

    echo "Status : Connected\n";
    echo "Driver : " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
    echo "Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;

    echo "===============================\n";

} catch (Throwable $e) {

    echo "===============================\n";
    echo "Status : Failed\n";
    echo "Message: {$e->getMessage()}\n";
    echo "===============================\n";

}