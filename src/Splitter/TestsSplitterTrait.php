<?php
declare(strict_types = 1);

namespace Codeception\Task\Splitter;

trait TestsSplitterTrait
{
    /**
     * @param $numGroups
     *
     * @return TestsSplitterTask
     */
    protected function taskSplitTestsByGroups($numGroups): TestsSplitterTask
    {
        return $this->task(TestsSplitterTask::class, $numGroups);
    }

    /**
     * @param $numGroups
     *
     * @return TestFileSplitterTask
     */
    protected function taskSplitTestFilesByGroups($numGroups): TestFileSplitterTask
    {
        return $this->task(TestFileSplitterTask::class, $numGroups);
    }
}
