<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Taladb\Connection\ConnectionInterface;
use Taladb\Manager\ConnectionManager;

final class ConnectionManagerTest extends TestCase
{
    public function testCanStoreMultipleConnections(): void
    {
        $manager = new ConnectionManager();


        $connection = $this->createStub(
            ConnectionInterface::class
        );


        $manager->add(
            'test',
            $connection
        );


        $this->assertTrue(
            $manager->has('test')
        );


        $this->assertSame(
            $connection,
            $manager->get('test')
        );
    }
}