<?php

namespace Tests\Codeception\Task\fixtures\DependencyResolutionExampleTests2\DirA;

use PHPUnit\Framework\TestCase;

class Example1Test extends TestCase
{

    /**
     * @depends testB
     * @group example
     */
    public function testA()
    {
        $this->markTestSkipped('Just a test ... test');
    }

    /**
     * @depends testA
     * @group example
     */
    public function testB()
    {
        $this->markTestSkipped('Just a test ... test');
    }
}
