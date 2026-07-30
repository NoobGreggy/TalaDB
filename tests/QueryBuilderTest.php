<?php

declare(strict_types=1);

namespace Tests;

class QueryBuilderTest extends TestCase
{
    public function testCanGetAllRecords(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@example.com',
            ]
        );

        $users = $this->db
            ->table('users')
            ->get();

        $this->assertNotEmpty($users);

        $this->assertIsArray($users);
    }

    public function testCanGetFirstRecord(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'greggys query builder',
                'email' => 'john@example.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->first();

        $this->assertNotNull($user);

        $this->assertArrayHasKey(
            'id',
            $user
        );
    }
}