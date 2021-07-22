<?php

namespace Tests\Codeception\Task\fixtures\Unit;

use PHPUnit\Framework\TestCase;

class ExampleATest extends TestCase
{

    /**
     * @group foo
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
     * @group bar
     * @group no
     * @group example
     */
    public function testB(): void
    {
        $this->assertTrue(false);
    }
}
