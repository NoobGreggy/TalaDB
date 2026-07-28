<?php

declare(strict_types=1);

namespace Tests;


class FindTest extends TestCase
{
    public function testFindUser(): void
    {
        $id = $this->db->insert(
            'users',
            [
                'name' => 'Find Test',
                'email' => 'find@test.com'
            ]
        );


        $user = $this->db->find(
            'users',
            $id
        );


        $this->assertNotNull(
            $user
        );


        $this->assertEquals(
            'Find Test',
            $user['name']
        );
    }



    public function testFindReturnsNullWhenMissing(): void
    {
        $user = $this->db->find(
            'users',
            999999
        );


        $this->assertNull(
            $user
        );
    }
}