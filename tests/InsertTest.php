<?php

declare(strict_types=1);

namespace Tests;


class InsertTest extends TestCase
{

    public function testInsertUser(): void
    {

        $id = $this->db->insert(
            'users',
            [
                'name'=>'Test User',
                'email'=>'test@example.com'
            ]
        );


        $this->assertNotEmpty(
            $id
        );

    }

}