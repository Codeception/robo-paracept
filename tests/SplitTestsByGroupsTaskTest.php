<?php
//require_once "/home/davert/projects/Robo/vendor/autoload.php";
//require_once "/home/davert/Codeception/autoload.php";

class SplitTestsByGroupsTaskTest extends PHPUnit_Framework_TestCase {

    use \Codeception\Task\SplitTestsByGroups;

    public function testGroupsCanBeSplit()
    {
        $this->taskSplitTestsByGroups(10)
            ->testsFrom(realpath(__DIR__.'/../vendor/codeception/codeception/tests/unit/Codeception/Command'))
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 10; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
    }

    public function testSplitFilesByGroups()
    {
        $this->taskSplitTestFilesByGroups(5)
            ->testsFrom(realpath(__DIR__.'/../vendor/codeception/codeception/tests/unit/Codeception/Command'))
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 5; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
    }

    public function setUp()
    {
        for ($i = 1; $i <= 10; $i++) {
            @unlink("tests/result/group_$i");
        }
    }
    
}
 