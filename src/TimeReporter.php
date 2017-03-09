<?php
namespace Codeception\Task;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;

class TimeReporter extends Extension
{
    public static $events = [
        Events::RESULT_PRINT_AFTER => 'endRun',
        Events::TEST_END => 'after',
    ];

    private $timeList = [];

    /**
     * Event handler after each test - collect stat
     *
     * @param \Codeception\Event\TestEvent $e
     */
    public function after(TestEvent $e)
    {
        $name = Descriptor::getTestFullName($e->getTest());
        $name = str_replace($this->getRootDir(), '', $name);

        if (empty($this->timeList[$name])) {
            $this->timeList[$name] = 0;
        }
        $this->timeList[$name] += $e->getTime();
    }

    /**
     * Event handler after all tests - save stat
     */
    public function endRun()
    {
        $file = $this->getLogDir() . 'timeReport.json';
        $data = is_file($file) ? json_decode(file_get_contents($file), true) : [];
        $data = array_replace($data, $this->timeList);
        file_put_contents($file, json_encode($data));
    }
}
