<?php

namespace Tests\Codeception\Task\fixtures\DependencyResolutionExampleTests\DirA;

use PHPUnit\Framework\TestCase;

class Example1Test extends TestCase
{

    /**
     * @depends testB
     * @group example
     */
    public function testA()
    {
        self::assertTrue(true);
    }


    /**
     * @group example
     */
    public function testB()
    {
        self::assertTrue(true);
    }

    /**
     * @depends testA
     * @group example
     */
    public function testC()
    {
        self::assertTrue(true);
    }
}
