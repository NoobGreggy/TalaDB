<?php

declare(strict_types=1);

namespace Taladb\Manager;

use Taladb\CRUD\Insert;
use Taladb\CRUD\Update;
use Taladb\CRUD\Delete;
use Taladb\CRUD\Find;
use Taladb\Query\QueryExecutor;

final class CRUDManager
{
    private Insert $insert;

    private Update $update;

    private Delete $delete;

    private Find $find;


    public function __construct(
        QueryExecutor $executor
    ) {

        $this->insert = new Insert(
            $executor
        );


        $this->update = new Update(
            $executor
        );


        $this->delete = new Delete(
            $executor
        );


        $this->find = new Find(
            $executor
        );
    }



    public function insert(): Insert
    {
        return $this->insert;
    }


    public function update(): Update
    {
        return $this->update;
    }


    public function delete(): Delete
    {
        return $this->delete;
    }


    public function find(): Find
    {
        return $this->find;
    }
}