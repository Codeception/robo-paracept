<?php

namespace Tests\Codeception\Task\fixtures\Unit;

use PHPUnit\Framework\TestCase;

class ExampleBTest extends TestCase
{

    /**
     * @group baz
     * @group bar
     * @group example
     * @depends testB
     */
    public function testA(): void
    {
        $this->assertTrue(false);
    }

    /**
     * @group foo
     * @group baz
     * @group no
     * @group example
     */
    public function testB(): void
    {
        $this->assertTrue(false);
    }
}
