<?php

class Example2Test extends \PHPUnit\Framework\TestCase{


    /**
     * @depends Example2Test::testE
     * @group example
     */
    public function testD(){
        self::assertTrue(true);
    }

    /**
     * @group example
     */
    public function testE(){
        self::assertTrue(true);
    }
}
