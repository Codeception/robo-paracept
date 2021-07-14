<?php

namespace Tests\Codeception\Task\Splitter;

use Codeception\Task\Splitter\SplitTestsByTimeTask;
use Codeception\Task\Splitter\TestsSplitterTrait;
use Consolidation\Log\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class SplitTestsByTimeTaskTest
 * @coversDefaultClass \Codeception\Task\Splitter\SplitTestsByTimeTask
 */
class SplitTestsByTimeTaskTest extends TestCase
{
    use TestsSplitterTrait;

    /**
     * @covers ::run
     */
    public function testRun(): void
    {
        $expectedFiles = 4;
        $task = new SplitTestsByTimeTask(4);
        $groupTo = TEST_PATH . '/result/group_';
        $task->setLogger(new Logger(new NullOutput()));
        $task->statFile(TEST_PATH . '/fixtures/timeReport.json')
            ->projectRoot(TEST_PATH . '/../')
            ->testsFrom(TEST_PATH . '/fixtures/')
            ->groupsTo($groupTo)
            ->run();


        for ($i = 1; $i <= $expectedFiles; $i++) {
            $this->assertFileExists($groupTo . $i);
        }

        $this->assertFileDoesNotExist($groupTo . ($expectedFiles + 1));
    }
}
