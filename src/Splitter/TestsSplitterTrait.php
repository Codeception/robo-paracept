<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use Robo\Collection\CollectionBuilder;

trait TestsSplitterTrait
{
    /**
     * @param int $numGroups
     *
     * @return TestsSplitterTask|CollectionBuilder
     */
    protected function taskSplitTestsByGroups(int $numGroups)
    {
        return $this->task(TestsSplitterTask::class, $numGroups);
    }

    /**
     * @param int $numGroups
     *
     * @return TestFileSplitterTask|CollectionBuilder
     */
    protected function taskSplitTestFilesByGroups(int $numGroups)
    {
        return $this->task(TestFileSplitterTask::class, $numGroups);
    }

    /**
     * @param int $numGroups
     *
     * @return TestFileSplitterTask|CollectionBuilder
     */
    protected function taskSplitTestsByTime(int $numGroups)
    {
        return $this->task(SplitTestsByTimeTask::class, $numGroups);
    }

    /**
     * @param int $numGroups
     *
     * @return TestFileSplitterTask|CollectionBuilder
     */
    protected function taskSplitFailedTests(int $numGroups)
    {
        return $this->task(FailedTestSplitterTask::class, $numGroups);
    }
}
