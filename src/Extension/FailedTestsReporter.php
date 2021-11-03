<?php

declare(strict_types=1);

namespace Codeception\Task\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

/**
 * Class FailedTestsReporter - reports the failed tests to a reportfile with
 * a unique name for parallel execution.
 *
 * Pattern is '/failedTests_\w+\.\w+\.txt/'
 *
 * Modify the codeception.yml to enable this extension:
 *  extensions:
 *      enabled:
 *          - Codeception\Task\Extension\FailedTestsReporter
 */
class FailedTestsReporter extends Extension
{
    /** @var string */
    public const REPORT_NAME = 'failedTests';

    private array $failedTests = [];

    /**
     * @var string[] $events
     */
    public static array $events = [
        Events::TEST_FAIL => 'afterFail',
        Events::RESULT_PRINT_AFTER => 'endRun',
    ];

    /**
     * Event after each failed test - collect the failed test
     */
    public function afterFail(FailEvent $event): void
    {
        $this->failedTests[] = $this->getTestName($event);
    }

    /**
     * Event after all Tests - write failed tests to report file
     */
    public function endRun(): void
    {
        if (empty($this->failedTests)) {
            return;
        }

        $file = $this->getLogDir() . $this->getUniqReportFile();
        if (is_file($file)) {
            unlink($file); // remove old reportFile
        }

        file_put_contents($file, implode(PHP_EOL, $this->failedTests));
    }

    public function getTestName(TestEvent $e): string
    {
        $name = Descriptor::getTestFullName($e->getTest());

        return substr(str_replace($this->getRootDir(), '', $name), 1);
    }

    public function getUniqReportFile(): string
    {
        return self::REPORT_NAME . '_' . uniqid('', true) . '.txt';
    }
}
