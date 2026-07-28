<?php

declare(strict_types=1);

namespace Tests;


class UpdateTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $id = $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@example.com',
            ]
        );

        $affected = $this->db->update(
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
}