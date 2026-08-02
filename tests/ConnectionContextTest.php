<?php

declare(strict_types=1);

namespace Tests;

use Taladb\Connection\ConnectionContext;

class ConnectionContextTest extends TestCase
{
    public function testReturnsConnectionContext(): void
    {
        $this->seedUsers();
        $context = $this->db->connection();

        $this->assertInstanceOf(
            ConnectionContext::class,
            $context
        );
    }

    public function testCanExecuteRawQuery(): void
    {
        $this->seedUsers();
        $result = $this->db
            ->connection()
            ->query("SELECT * FROM users");

        $this->assertNotEmpty(
            $result->all()
        );
    }

    public function testCanInsertRecord(): void
    {
        $this->seedUsers();
        $id = $this->db
            ->connection()
            ->insert(
                'users',
                [
                    'name' => 'Gregg',
                    'email' => 'gregg@example.com',
                ]
            );

        $this->assertNotEmpty($id);
    }

    public function testCanUpdateRecord(): void
    {
        $id = $this->db
            ->connection()
            ->insert(
                'users',
                [
                    'name' => 'Gregg',
                    'email' => 'gregg@example.com',
                ]
            );

        $affected = $this->db
            ->connection()
            ->update(
                'users',
                [
                    'name' => 'Updated User',
                ],
                [
                    'id' => (int) $id,
                ]
            );

        $this->assertGreaterThan(
            0,
            $affected
        );
    }

    public function testCanFindRecord(): void
    {
        $id = $this->db
            ->connection()
            ->insert(
                'users',
                [
                    'name' => 'Gregg',
                    'email' => 'gregg@example.com',
                ]
            );

        $user = $this->db
            ->connection()
            ->find(
                'users',
                (int) $id
            );

        $this->assertNotNull($user);

        $this->assertEquals(
            (int) $id,
            $user['id']
        );
    }

    public function testCanDeleteRecord(): void
    {
        $id = $this->db
            ->connection()
            ->insert(
                'users',
                [
                    'name' => 'Gregg',
                    'email' => 'gregg@example.com',
                ]
            );

        $affected = $this->db
            ->connection()
            ->delete(
                'users',
                [
                    'id' => (int) $id,
                ]
            );

        $this->assertGreaterThan(
            0,
            $affected
        );
    }

    public function testCanReturnUnderlyingConnection(): void
    {

        $connection = $this->db
            ->connection()
            ->getConnection();

        $this->assertInstanceOf(
            \Taladb\Connection\ConnectionInterface::class,
            $connection
        );
    }
}