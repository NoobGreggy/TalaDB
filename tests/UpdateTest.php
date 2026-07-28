<?php

declare(strict_types=1);

namespace Tests;


class UpdateTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $affected = $this->db->update(
            'users',
            [
                'name' => 'Updated User ' . time()
            ],
            [
                'id' => 1
            ]
        );


        $this->assertGreaterThan(
            0,
            $affected
        );
    }
}