<?php

class SplitTestsByGroupsTaskTest extends \PHPUnit\Framework\TestCase
{
    use \Codeception\Task\SplitTestsByGroups;

    public function testGroupsCanBeSplit()
    {
        $task = new Codeception\Task\SplitTestsByGroupsTask(10);
        $task->setLogger(new \Consolidation\Log\Logger(new \Symfony\Component\Console\Output\NullOutput()));
        $task->testsFrom('vendor/codeception/base/tests/unit/Codeception/Command')
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 10; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
        $this->assertFileNotExists("tests/result/group_11");
    }

    public function testSplitFilesByGroups()
    {
        $task = new Codeception\Task\SplitTestsByGroupsTask(5);
        $task->setLogger(new \Consolidation\Log\Logger(new \Symfony\Component\Console\Output\NullOutput()));
        $task->testsFrom('vendor/codeception/base/tests/unit/Codeception/Command')
            ->projectRoot('vendor/codeception/base/')
            ->groupsTo('tests/result/group_')
            ->run();

        for ($i = 1; $i <= 5; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }
    }

    /**
     * Test Circular dependency protection
     *
     * @throws \Robo\Exception\TaskException
     */
    public function testCircularDependencyDetectionAndHandling(){
        $task = new Codeception\Task\SplitTestsByGroupsTask(5);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $task->setLogger(new \Consolidation\Log\Logger($output));
        $task->testsFrom('tests/fixtures/DependencyResolutionExampleTests2')
            ->projectRoot('vendor/codeception/base/')
            ->groupsTo('tests/result/group_')
            ->run();

        $d = $output->fetch();

        self::assertContains('Circular dependency:', $d);

        // make sure that no files were generated.
        $this->assertEmpty(glob("tests/result/group_*"));
    }

    /**
     * Test dependency resolving
     *
     * @throws \Robo\Exception\TaskException
     */
    public function testDependencyResolving(){

        $task = new Codeception\Task\SplitTestsByGroupsTask(2);
        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        $task->setLogger(new \Consolidation\Log\Logger($output));

        $task->testsFrom('tests/fixtures/DependencyResolutionExampleTests')
             ->projectRoot('vendor/codeception/base/')
             ->groupsTo('tests/result/group_')
             ->run();
        for ($i = 1; $i <= 2; $i++) {
            $this->assertFileExists("tests/result/group_$i");
        }

        // because path might be different on every system we need only last part of the path.
        $firstFile = file_get_contents("tests/result/group_1");
        $lines = [];
        foreach(explode("\n", $firstFile) as $line) {
            $lines[] = substr($line, -22);
        }
        // correct order of test execution is preserved
        self::assertSame(['Example1Test.php:testB', 'Example1Test.php:testA', 'Example1Test.php:testC'], $lines);

        // check second file.
        $secondFile = file_get_contents("tests/result/group_2");
        $lines = [];
        foreach(explode("\n", $secondFile) as $line) {
            $lines[] = substr($line, -22);
        }
        // correct order of test execution is preserved
        self::assertSame(['Example2Test.php:testE', 'Example2Test.php:testD', 'Example3Test.php:testF', 'Example3Test.php:testG'], $lines);
    }

    public function setUp()
    {
        @mkdir('tests/result');

        // remove all files even from bad runs.
        foreach(glob('tests/result/group_*') as $file) {
            $file = new SplFileInfo($file);
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }



}
