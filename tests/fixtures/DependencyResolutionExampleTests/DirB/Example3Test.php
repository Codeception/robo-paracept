<?php

namespace Tests\Codeception\Task\fixtures\DependencyResolutionExampleTests\DirB;

use PHPUnit\Framework\TestCase;

class Example3Test extends TestCase
{

    /**
     * @group example
     */
    public function testF()
    {
        self::assertTrue(true);
    }

    /**
     * @group example
     */
    public function testG()
    {
        self::assertTrue(true);
    }
}
