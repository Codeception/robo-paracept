<?php
declare(strict_types = 1);

namespace Codeception\Task;

trait SplitTestsByGroupsTrait
{
    /**
     * @param $numGroups
     *
     * @return SplitTestsByGroupsTask
     */
    protected function taskSplitTestsByGroups($numGroups)
    {
        return $this->task(SplitTestsByGroupsTask::class, $numGroups);
    }

    /**
     * @param $numGroups
     *
     * @return SplitTestFilesByGroupsTask
     */
    protected function taskSplitTestFilesByGroups($numGroups)
    {
        return $this->task(SplitTestFilesByGroupsTask::class, $numGroups);
    }
}