<?php

namespace Tests\Codeception\Task\Splitter;

use Codeception\Task\Splitter\TestsSplitterTask;
use PHPUnit\Framework\TestCase;
use Robo\Exception\TaskException;
use Symfony\Component\Finder\Finder;

/**
 * Class TestsSplitterTaskTest
 * @coversDefaultClass \Codeception\Task\Splitter\TestsSplitterTask
 */
class TestsSplitterTaskTest extends TestCase
{
    public function testRunThrowsExceptionIfCodeceptLoaderIsNotLoaded(): void
    {
        $service = $this->getMockBuilder(TestsSplitterTask::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['doCodeceptLoaderExists'])
            ->getMock();

        $service
            ->method('doCodeceptLoaderExists')
            ->willReturn(false);

        $this->expectException(TaskException::class);
        $this->expectErrorMessage(
            'This task requires Codeception to be loaded.'
            . ' Please require autoload.php of Codeception'
        );
        $service->run();
    }

    public function providerTestLoadTestsWithDifferentPatterns(): array
    {
        return [
            [
                'groups' => 1,
                'expectedFiles' => 7,
            ],
            [
                'groups' => 2,
                'expectedFiles' => 7,
            ],
            [
                'groups' => 7,
                'expectedFiles' => 7,
            ],
        ];
    }

    /**
     * @covers ::run
     * @dataProvider providerTestLoadTestsWithDifferentPatterns
     * @param int $groups
     * @param int $expectedFiles
     */
    public function testLoadTests(
        int $groups,
        int $expectedFiles
    ): void {
        $task = new TestsSplitterTask($groups);
        $task->testsFrom(TEST_PATH . '/fixtures/');
        $groupTo = TEST_PATH . '/result/group_';
        $task->groupsTo($groupTo);
        $task->run();

        $files = Finder::create()
            ->files()
            ->in(TEST_PATH . '/result/')
            ->name('group_*');

        $this->assertCount($groups, $files->getIterator());

        for ($i = 1; $i <= $groups; $i++) {
            $this->assertFileExists($groupTo . $i);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        $finder = Finder::create()
            ->files()
            ->name('group_*');

        foreach ($finder->in(TEST_PATH . '/result') as $file) {
            unlink($file->getPathname());
        }
    }
}
