<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

trait ReportMerger
{
    protected function taskMergeXmlReports($src = [])
    {
        return $this->task(XmlReportMergerTask::class, $src);
    }

    protected function taskMergeHTMLReports($src = [])
    {
        return $this->task(HtmlReportMerger::class, $src);
    }
}
