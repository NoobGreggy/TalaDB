<?php

declare(strict_types=1);

namespace Tests;


class ResultTest extends TestCase
{

    public function testCanFetchAll(): void
    {
        $result = $this->db->query(
            "SELECT * FROM users"
        );


        $users = $result->all();


        $this->assertIsArray(
            $users
        );
    }



    public function testCanFetchFirst(): void
    {
        $user = $this->db
            ->query(
                "SELECT * FROM users"
            )
            ->first();


        $this->assertNotNull(
            $user
        );
    }



    public function testCanCountResults(): void
    {
        $count = $this->db
            ->query(
                "SELECT * FROM users"
            )
            ->count();


        $this->assertGreaterThan(
            0,
            $count
        );
    }

    public function testUpdateReturnsAffectedRows(): void
    {
        $result = $this->db->query(
            "UPDATE users 
         SET name=? 
         WHERE id=?",
            [
                "Gregg " . time(),
                1
            ]
        );


        $this->assertGreaterThan(
            0,
            $result->affected()
        );
    }



}