<?php

declare(strict_types=1);

namespace Codeception\Task\Extension;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

/**
 * Class FailedTestsReporter - reports the failed tests to a reportfile
 * Modify the codeception.yml to enable this extension:
 *  extensions:
 *      enabled:
 *          - Codeception\Task\Extension\FailedTestsReporter
 */
class FailedTestsReporter extends Extension
{
    /** @var string $reportFile */
    private $reportFile = 'failedTests.txt';
    /** @var array $failedTests */
    private $failedTests = [];

    /**
     * @var string[] $events
     */
    public static $events = [
        Events::TEST_FAIL => 'afterFail',
        Events::RESULT_PRINT_AFTER => 'endRun',
    ];

    /**
     * Event after each failed test - collect the failed test
     * @param FailEvent $event
     */
    public function afterFail(FailEvent $event): void
    {
        $this->failedTests[] = $this->getTestname($event);
    }

    /**
     * Event after all Tests - write failed tests to reportfile
     */
    public function endRun(): void
    {
        if (empty($this->failedTests)) {
            return;
        }

        $file = $this->getLogDir() . $this->reportFile;
        if (is_file($file)) {
            unlink($file); // remove old reportFile
        }

        file_put_contents($file, implode(PHP_EOL, $this->failedTests));
    }

    /**
     * @param TestEvent $e
     * @return false|string
     */
    public function getTestname(TestEvent $e): string
    {
        $name = Descriptor::getTestFullName($e->getTest());

        return substr(str_replace($this->getRootDir(), '', $name), 1);
    }
}
