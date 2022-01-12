<?php

declare(strict_types=1);

namespace Codeception\Task\Extension;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

class TimeReporter extends Extension
{
    public static array $events = [
        Events::RESULT_PRINT_AFTER => 'endRun',
        Events::TEST_END => 'after',
    ];

    private array $timeList = [];

    /**
     * Event handler after each test - collect stat
     */
    public function after(TestEvent $e): void
    {
        $name = $this->getTestName($e);

        if (empty($this->timeList[$name])) {
            $this->timeList[$name] = 0;
        }
        $this->timeList[$name] += $e->getTime();
    }

    /**
     * Event handler after all tests - save stat
     */
    public function endRun(): void
    {
        $file = $this->getLogDir() . 'timeReport.json';
        $data = is_file($file) ? json_decode(file_get_contents($file), true) : [];
        $data = array_replace($data, $this->timeList);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function getTestName(TestEvent $e): string
    {
        $name = Descriptor::getTestFullName($e->getTest());
        return substr(str_replace($this->getRootDir(), '', $name), 1);
    }
}
