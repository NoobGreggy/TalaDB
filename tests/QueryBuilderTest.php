<?php

declare(strict_types=1);

namespace Tests;

class QueryBuilderTest extends TestCase
{
    public function testCanFilterById(): void
    {
        $id = $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@example.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where('id', $id)
            ->first();

        $this->assertNotNull($user);

        $this->assertEquals(
            $id,
            $user['id']
        );
    }

    public function testCanFilterByEmail(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'John',
                'email' => 'john@example.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where(
                'email',
                'john@example.com'
            )
            ->first();

        $this->assertNotNull($user);

        $this->assertEquals(
            'john@example.com',
            $user['email']
        );
    }

    public function testCanWhereEquals(): void
    {
        $id = $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@test.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where('id', '=', (int) $id)
            ->first();

        $this->assertNotNull($user);

        $this->assertEquals(
            $id,
            $user['id']
        );
    }

    public function testCanWhereGreaterThan(): void
    {
        $firstId = $this->db->insert(
            'users',
            [
                'name' => 'First',
                'email' => 'first@test.com',
            ]
        );

        $secondId = $this->db->insert(
            'users',
            [
                'name' => 'Second',
                'email' => 'second@test.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where('id', '>', (int) $firstId)
            ->first();

        $this->assertNotNull($user);

        $this->assertGreaterThan(
            $firstId,
            $user['id']
        );
    }

    public function testCanWhereNotEquals(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@test.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where('name', '!=', 'Nobody')
            ->first();

        $this->assertNotNull($user);

        $this->assertNotEquals(
            'Nobody',
            $user['name']
        );
    }

    public function testCanWhereLike(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'Gregg',
                'email' => 'gregg@gmail.com',
            ]
        );

        $user = $this->db
            ->table('users')
            ->where('email', 'LIKE', '%gmail.com')
            ->first();

        $this->assertNotNull($user);

        $this->assertStringEndsWith(
            'gmail.com',
            $user['email']
        );
    }

    public function testCanOrderByAscending(): void
    {
        $this->db->insert(
            'users',
            [
                'name' => 'Charlie',
                'email' => 'charlie@test.com',
            ]
        );

        $this->db->insert(
            'users',
            [
                'name' => 'Alice',
                'email' => 'alice@test.com',
            ]
        );

        $users = $this->db
            ->table('users')
            ->orderBy('name')
            ->get();

        $this->assertEquals(
            'Alice',
            $users[0]['name']
        );
    }

    public function testCanOrderByDescending(): void
    {
        $this->db->insert('users', [
            'name' => 'Alice',
            'email' => 'alice@test.com',
        ]);

        $this->db->insert('users', [
            'name' => 'Charlie',
            'email' => 'charlie@test.com',
        ]);

        $users = $this->db
            ->table('users')
            ->orderBy('name', 'DESC')
            ->get();

        $names = array_column($users, 'name');

        $this->assertContains('Alice', $names);
        $this->assertContains('Charlie', $names);

        $aliceIndex = array_search('Alice', $names, true);
        $charlieIndex = array_search('Charlie', $names, true);

        $this->assertLessThan(
            $aliceIndex,
            $charlieIndex
        );
    }

    public function testCanLimitResults(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->limit(2)
            ->get();

        $this->assertCount(
            2,
            $users
        );
    }

    public function testLimitOneMatchesFirst(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->limit(1)
            ->get();

        $this->assertCount(
            1,
            $users
        );
    }

    public function testCanOffsetResults(): void
    {
        $this->db->insert('users', [
            'name' => 'Alice',
            'email' => 'alice@test.com',
        ]);

        $this->db->insert('users', [
            'name' => 'Bob',
            'email' => 'bob@test.com',
        ]);

        $this->db->insert('users', [
            'name' => 'Charlie',
            'email' => 'charlie@test.com',
        ]);

        $users = $this->db
            ->table('users')
            ->orderBy('id')
            ->limit(2)
            ->offset(1)
            ->get();

        $this->assertCount(
            2,
            $users
        );

        $this->assertEquals(
            'Bob',
            $users[0]['name']
        );

        $this->assertEquals(
            'Charlie',
            $users[1]['name']
        );
    }


    public function testOffsetBeyondAvailableRows(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->limit(10)
            ->offset(100)
            ->get();

        $this->assertEmpty(
            $users
        );
    }

    public function testCanSelectSingleColumn(): void
    {
        $this->seedUsers();

        $user = $this->db
            ->table('users')
            ->select('name')
            ->first();

        $this->assertArrayHasKey(
            'name',
            $user
        );

        $this->assertArrayNotHasKey(
            'email',
            $user
        );
    }

    public function testCanSelectMultipleColumns(): void
    {
        $this->seedUsers();

        $user = $this->db
            ->table('users')
            ->select('id', 'email')
            ->first();

        $this->assertArrayHasKey(
            'id',
            $user
        );

        $this->assertArrayHasKey(
            'email',
            $user
        );

        $this->assertArrayNotHasKey(
            'name',
            $user
        );
    }

    public function testCanSelectWithWhere(): void
    {
        $this->seedUsers();

        $user = $this->db
            ->table('users')
            ->select('name')
            ->where('email', 'john@test.com')
            ->first();

        $this->assertEquals(
            'John',
            $user['name']
        );
    }

    public function testCanUseOrWhere(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->where('name', 'John')
            ->orWhere('name', 'Jane')
            ->get();

        $this->assertCount(
            2,
            $users
        );
    }

    public function testCanCombineWhereAndOrWhere(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->where('id', '>', 0)
            ->orWhere('email', 'john@test.com')
            ->get();

        $this->assertGreaterThan(
            0,
            count($users)
        );
    }

    public function testCanUseWhereIn(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->whereIn('id', [1, 3])
            ->get();

        $this->assertCount(
            2,
            $users
        );

        $this->assertEquals(
            1,
            $users[0]['id']
        );

        $this->assertEquals(
            3,
            $users[1]['id']
        );
    }

    public function testCanUseWhereInWithSingleValue(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->whereIn('id', [2])
            ->get();

        $this->assertCount(
            1,
            $users
        );

        $this->assertEquals(
            2,
            $users[0]['id']
        );
    }

    public function testWhereInReturnsEmptyArrayWhenNoMatches(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->whereIn('id', [100, 200])
            ->get();

        $this->assertEmpty(
            $users
        );
    }

    public function testCanUseWhereNull(): void
    {
        $this->db->query("
                CREATE TEMPORARY TABLE test_nulls (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nickname VARCHAR(100) NULL
                )
            ");

                $this->db->query("
                INSERT INTO test_nulls (nickname)
                VALUES (NULL), ('Gregg')
            ");

        $users = $this->db
            ->table('test_nulls')
            ->whereNull('nickname')
            ->get();

        $this->assertCount(
            1,
            $users
        );

        $this->assertNull(
            $users[0]['nickname']
        );
    }

    public function testCanUseWhereNotNull(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE test_not_null (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nickname VARCHAR(100) NULL
        )
    ");

        $this->db->query("
        INSERT INTO test_not_null (nickname)
        VALUES (NULL), ('Gregg')
    ");

        $users = $this->db
            ->table('test_not_null')
            ->whereNotNull('nickname')
            ->get();

        $this->assertCount(
            1,
            $users
        );

        $this->assertEquals(
            'Gregg',
            $users[0]['nickname']
        );
    }

    public function testCanUseWhereBetween(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->whereBetween(
                'id',
                [1, 2]
            )
            ->get();

        $this->assertCount(
            2,
            $users
        );

        $this->assertEquals(
            1,
            $users[0]['id']
        );

        $this->assertEquals(
            2,
            $users[1]['id']
        );
    }


    public function testCanUseWhereNotBetween(): void
    {
        $this->seedUsers();

        $users = $this->db
            ->table('users')
            ->whereNotBetween(
                'id',
                [1, 2]
            )
            ->get();

        $this->assertCount(
            1,
            $users
        );

        $this->assertEquals(
            3,
            $users[0]['id']
        );
    }


    public function testCanJoinTables(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255)
        )
    ");

        $this->seedUsers();

        $this->db->query("
        INSERT INTO posts (user_id, title)
        VALUES
        (1, 'First Post'),
        (2, 'Second Post')
    ");

        $rows = $this->db
            ->table('users')
            ->join(
                'posts',
                'users.id',
                '=',
                'posts.user_id'
            )
            ->get();

        $this->assertCount(
            2,
            $rows
        );
    }

    public function testCanLeftJoinTables(): void
    {
        $this->seedUsers();

        $this->db->query("
        CREATE TEMPORARY TABLE posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255)
        )
    ");

        $this->db->query("
        INSERT INTO posts (user_id, title)
        VALUES
        (1, 'First Post'),
        (2, 'Second Post')
    ");

        $rows = $this->db
            ->table('users')
            ->leftJoin(
                'posts',
                'users.id',
                '=',
                'posts.user_id'
            )
            ->get();

        // All users should be returned.
        $this->assertCount(
            3,
            $rows
        );

        // Third user has no matching post.
        $this->assertNull(
            $rows[2]['title']
        );
    }


    public function testCanRightJoinTables(): void
    {
        $this->seedUsers();

        $this->db->query("
        CREATE TEMPORARY TABLE posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            title VARCHAR(255)
        )
    ");

        $this->db->query("
        INSERT INTO posts (user_id, title)
        VALUES
        (1, 'First Post'),
        (2, 'Second Post'),
        (99, 'Orphan Post')
    ");

        $rows = $this->db
            ->table('users')
            ->rightJoin(
                'posts',
                'users.id',
                '=',
                'posts.user_id'
            )
            ->get();

        // Every post should be returned.
        $this->assertCount(
            3,
            $rows
        );

        // The last post has no matching user.
        $this->assertNull(
            $rows[2]['name']
        );

        $this->assertEquals(
            'Orphan Post',
            $rows[2]['title']
        );
    }

    public function testCanGroupBySingleColumn(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(50)
        )
    ");

        $this->db->query("
        INSERT INTO sales (department)
        VALUES
        ('IT'),
        ('IT'),
        ('HR'),
        ('Finance')
    ");

        $rows = $this->db
            ->table('sales')
            ->select('department')
            ->groupBy('department')
            ->get();

        $this->assertCount(
            3,
            $rows
        );
    }


    public function testCanGroupByMultipleColumns(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(50),
            status VARCHAR(20)
        )
    ");

        $this->db->query("
        INSERT INTO sales (department, status)
        VALUES
        ('IT','Open'),
        ('IT','Closed'),
        ('IT','Open'),
        ('HR','Open')
    ");

        $rows = $this->db
            ->table('sales')
            ->select(
                'department',
                'status'
            )
            ->groupBy(
                'department',
                'status'
            )
            ->orderBy('department')
            ->orderBy('status')
            ->get();

        $this->assertCount(
            3,
            $rows
        );
    }


    public function testCanSelectDistinct(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE colors (
            id INT AUTO_INCREMENT PRIMARY KEY,
            color VARCHAR(50)
        )
    ");

        $this->db->query("
        INSERT INTO colors (color)
        VALUES
        ('Red'),
        ('Blue'),
        ('Red'),
        ('Green')
    ");

        $rows = $this->db
            ->table('colors')
            ->select('color')
            ->distinct()
            ->orderBy('color')
            ->get();

        $this->assertCount(
            3,
            $rows
        );
    }

    public function testCanUseHaving(): void
    {
        $this->db->query("
        CREATE TEMPORARY TABLE sales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            department VARCHAR(50)
        )
    ");

        $this->db->query("
        INSERT INTO sales (department)
        VALUES
        ('IT'),
        ('IT'),
        ('HR'),
        ('Finance')
    ");

        $rows = $this->db
            ->table('sales')
            ->select(
                'department',
                'COUNT(*) AS total'
            )
            ->groupBy('department')
            ->having('total', '>', 1)
            ->get();

        $this->assertCount(
            1,
            $rows
        );

        $this->assertEquals(
            'IT',
            $rows[0]['department']
        );
    }

    public function testCanCountRecords(): void
    {
        $this->seedUsers();

        $count = $this->db
            ->table('users')
            ->count();

        $this->assertEquals(
            3,
            $count
        );
    }

    public function testCanGetMaximumValue(): void
    {
        $this->seedUsers();

        $max = $this->db
            ->table('users')
            ->max('id');

        $this->assertEquals(
            3,
            $max
        );
    }

    public function testCanGetMinimumValue(): void
    {
        $this->seedUsers();

        $min = $this->db
            ->table('users')
            ->min('id');

        $this->assertEquals(
            1,
            $min
        );
    }

    public function testCanGetAverageValue(): void
    {
        $this->seedUsers();

        $avg = $this->db
            ->table('users')
            ->avg('id');

        $this->assertEquals(
            2,
            $avg
        );
    }

    public function testCanGetSum(): void
    {
        $this->seedUsers();

        $sum = $this->db
            ->table('users')
            ->sum('id');

        $this->assertEquals(
            6,
            $sum
        );
    }

    public function testCanChunkResults(): void
    {
        $this->seedUsers();

        $count = 0;

        $this->db
            ->table('users')
            ->orderBy('id')
            ->chunk(2, function (array $users) use (&$count) {

                $count += count($users);

            });

        $this->assertEquals(
            3,
            $count
        );
    }

    public function testCanPaginateResults(): void
    {
        $this->seedUsers();

        $page = $this->db
            ->table('users')
            ->orderBy('id')
            ->paginate(
                2,
                1
            );

        $this->assertCount(
            2,
            $page['data']
        );

        $this->assertEquals(
            3,
            $page['total']
        );

        $this->assertEquals(
            2,
            $page['per_page']
        );

        $this->assertEquals(
            1,
            $page['current_page']
        );

        $this->assertEquals(
            2,
            $page['last_page']
        );
    }




}