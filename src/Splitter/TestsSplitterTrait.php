<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

trait TestsSplitterTrait
{
    /**
     * @param int $numGroups
     *
     * @return TestsSplitterTask
     */
    protected function taskSplitTestsByGroups(int $numGroups): TestsSplitterTask
    {
        return $this->task(TestsSplitterTask::class, $numGroups);
    }

    /**
     * @param int $numGroups
     *
     * @return TestFileSplitterTask
     */
    protected function taskSplitTestFilesByGroups(int $numGroups): TestFileSplitterTask
    {
        return $this->task(TestFileSplitterTask::class, $numGroups);
    }

    /**
     * @param $numGroups
     *
     * @return TestFileSplitterTask
     */
    protected function taskSplitTestsByTime($numGroups): TestFileSplitterTask
    {
        return $this->task(SplitTestsByTimeTask::class, $numGroups);
    }
}
