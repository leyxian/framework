<?php

namespace edao\model\traits;

trait Model 
{
    public function connect(string $conn)
    {
        $this->connection = $conn;
    }
}



