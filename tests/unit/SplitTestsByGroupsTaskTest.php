<?php

class SplitTestsByGroupsTaskTest extends \Codeception\TestCase\Test
{
    use \Codeception\Task\SplitTestsByGroups;

    public function testGroupsCanBeSplit()
    {
        $this->taskSplitTestsByGroups(10)
            ->testsFrom(realpath(__DIR__.'/../../vendor/codeception/codeception/tests/unit/Codeception/Command'))
            ->groupsTo('tests/unit/result/group_')
            ->run();

        for ($i = 1; $i <= 10; $i++) {
            $this->assertFileExists("tests/unit/result/group_$i");
        }
    }

    public function testSplitFilesByGroups()
    {
        $this->taskSplitTestFilesByGroups(5)
            ->testsFrom('tests/unit/Codeception/Command')
            ->projectRoot(realpath(__DIR__.'/../../vendor/codeception/codeception/'))
            ->groupsTo('tests/unit/result/group_')
            ->run();

        for ($i = 1; $i <= 5; $i++) {
            $this->assertFileExists("tests/unit/result/group_$i");
        }
    }

    public function setUp()
    {
        @mkdir('tests/unit/result');
        for ($i = 1; $i <= 10; $i++) {
            @unlink("tests/unit/result/group_$i");
        }
    }
}
