<?php

namespace Tests\Codeception\Task\fixtures\DependencyResolutionExampleTests\DirA;

use PHPUnit\Framework\TestCase;

class Example2Test extends TestCase
{


    /**
     * @depends testE
     * @group example
     */
    public function testD()
    {
        self::assertTrue(true);
    }

    /**
     * @group example
     */
    public function testE()
    {
        self::assertTrue(true);
    }
}
