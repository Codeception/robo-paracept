<?php

class Example4Test extends \PHPUnit_Framework_TestCase {

    /**
     * @depends testB
     */
    public function testA() {

    }

    /**
     * @depends testA
     */
    public function testB(){

    }
}
