<?php

class Example1Test extends \PHPUnit_Framework_TestCase {

    /**
     * @depends testB
     */
    public function testA() {

    }


    public function testB(){

    }

    /**
     * @depends testA
     */
    public function testC(){
        
    }
}
