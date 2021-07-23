<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use Robo\Collection\CollectionBuilder;

trait ReportMerger
{
    /**
     * @param array $src
     * @return CollectionBuilder|XmlReportMergerTask
     */
    protected function taskMergeXmlReports(array $src = [])
    {
        return $this->task(XmlReportMergerTask::class, $src);
    }
    
    /**
     * @param array $src
     * @return CollectionBuilder|HtmlReportMerger
     */
    protected function taskMergeHTMLReports(array $src = [])
    {
        return $this->task(HtmlReportMerger::class, $src);
    }
}
