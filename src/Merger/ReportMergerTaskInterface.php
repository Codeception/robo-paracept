<?php
declare(strict_types = 1);

namespace Codeception\Task\Merger;

interface ReportMergerTaskInterface
{
    /**
     * @param array|string $fileName
     * @return $this
     */
    public function from($fileName);


    /**
     * @param string $fileName
     * @return $this
     */
    public function into($fileName);
}