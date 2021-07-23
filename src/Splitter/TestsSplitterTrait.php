<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use Robo\Collection\CollectionBuilder;

trait TestsSplitterTrait
{
    /**
     * @param int $numGroups
     *
     * @return CollectionBuilder|TestsSplitterTask
     */
    protected function taskSplitTestsByGroups(int $numGroups)
    {
        return $this->task(TestsSplitterTask::class, $numGroups);
    }

    /**
     * @param int $numGroups
     *
     * @return CollectionBuilder|TestFileSplitterTask
     */
    protected function taskSplitTestFilesByGroups(int $numGroups)
    {
        return $this->task(TestFileSplitterTask::class, $numGroups);
    }

    /**
     * @param $numGroups
     *
     * @return CollectionBuilder|SplitTestsByTimeTask
     */
    protected function taskSplitTestsByTime($numGroups)
    {
        return $this->task(SplitTestsByTimeTask::class, $numGroups);
    }

    /**
     * @param $numGroups
     *
     * @return  CollectionBuilder|SplitTestsFilesByFailedTask
     */
    protected function taskSplitTestsFilesByFailed($numGroups)
    {
        return $this->task(SplitTestsFilesByFailedTask::class, $numGroups);
    }
}
