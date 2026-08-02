<?php

declare(strict_types=1);

namespace Tests;


class QueryExecutorTest extends TestCase
{

    public function testSelect(): void
    {
        $this->seedUsers();
        $result = $this->db->query(
            "SELECT * FROM users"
        );


        $this->assertNotEmpty(
            $result->all()
        );
    }

    public function testInsertReturnsLastInsertId(): void
    {
        $this->seedUsers();
        $result = $this->db->query(
            "INSERT INTO users(name,email)
         VALUES (?,?)",
            [
                "greggys new",
                "alice@test.com"
            ]
        );


        $id = $result->lastInsertId();


        $this->assertNotEmpty(
            $id
        );
    }



}