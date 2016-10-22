<?php

class SplitTestsByGroupsTaskTest extends PHPUnit_Framework_TestCase
{
    use \Codeception\Task\SplitTestsByGroups;

    public function testGroupsCanBeSplit()
    {
        $task = new Codeception\Task\SplitTestsByGroupsTask(10);
        $task->testsFrom(realpath(__DIR__.'/../vendor/codeception/base/tests/unit/Codeception/Command'))
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 10; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
    }

    public function testSplitFilesByGroups()
    {
        $task = new Codeception\Task\SplitTestsByGroupsTask(5);
        $task->testsFrom(realpath(__DIR__.'/../vendor/codeception/base/tests/unit/Codeception/Command'))
            ->projectRoot(realpath(__DIR__.'/../vendor/codeception/base/'))
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 5; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
    }

    public function setUp()
    {
        @mkdir('tests/result');
        for ($i = 1; $i <= 10; $i++) {
            @unlink("tests/result/group_$i");
        }
    }
}
