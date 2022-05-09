<?php

namespace edao\tests;

use edao\controller\Model;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{
    public function testModel()
    {
        $m = new Model();
        $res = $m->index(222);
        $this->assertEquals('执行结果', $res);
    }
}

