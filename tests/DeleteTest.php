<?php

declare(strict_types=1);

namespace Tests;


class DeleteTest extends TestCase
{
    public function testDeleteUser(): void
    {
        $id = $this->db->insert(
            'users',
            [
                'name' => 'Delete Test',
                'email' => 'delete@test.com'
            ]
        );


        $deleted = $this->db->delete(
            'users',
            [
                'id' => $id
            ]
        );


        $this->assertEquals(
            1,
            $deleted
        );
    }
}