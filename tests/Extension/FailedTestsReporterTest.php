<?php

namespace Tests\Codeception\Task\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Task\Extension\FailedTestsReporter;
use PHPUnit\Framework\TestCase;

/**
 * Class FailedTestsReporterTest
 * @coversDefaultClass \Codeception\Task\Extension\FailedTestsReporter
 */
class FailedTestsReporterTest extends TestCase
{
    private $failedTests = [
        ['testname' => 'tests/acceptance/bar/baz.php:testA',],
        ['testname' => 'tests/acceptance/bar/baz.php:testB',],
        ['testname' => 'tests/acceptance/bar/baz.php:testC',],
        ['testname' => 'tests/acceptance/bar/baz.php:testD',],
        ['testname' => 'tests/acceptance/bar/baz.php:testE',],
        ['testname' => 'tests/acceptance/bar/baz.php:testF',],
        ['testname' => 'tests/acceptance/bar/baz.php:testG',],
        ['testname' => 'tests/acceptance/bar/baz.php:testH',],
    ];

    /**
     * @covers ::endRun
     */
    public function testEndRun(): void
    {
        $reporter = $this->getMockBuilder(FailedTestsReporter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTestname', 'getLogDir'])
            ->getMock();

        $reporter->method('getLogDir')->willReturn(TEST_PATH . '/result/');

        // prepare Mocks for Test
        $testEvents = [];
        foreach ($this->failedTests as $test) {
            $eventMock = $this->getMockBuilder(FailEvent::class)
                ->disableOriginalConstructor()
                ->getMock();

            $testEvents[] = [
                'mock' => $eventMock,
                'testname' => $test['testname']
            ];
        }

        // get Testname by the TestEventMock
        $reporter
            ->method('getTestname')
            ->withConsecutive(
                ...array_map(
                    static function (FailEvent $event): array {
                        return [$event];
                    },
                    array_column($testEvents, 'mock')
                )
            )
            ->willReturnOnConsecutiveCalls(...array_column($testEvents, 'testname'));

        foreach ($testEvents as $event) {
            $reporter->afterFail($event['mock']);
        }

        $reporter->endRun();
        $file = TEST_PATH . '/result/failedTests.txt';
        $this->assertFileExists($file);
        $content = explode(PHP_EOL, file_get_contents($file));
        $this->assertCount(8, $content);
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        unlink(TEST_PATH . '/result/failedTests.txt');
    }
}