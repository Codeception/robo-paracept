<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use RuntimeException;

class FailedTestSplitterTask extends TestsSplitter
{
    /** @var string */
    private $reportPath = null;

    /**
     * @return string
     */
    public function getReportPath(): string
    {
        return $this->reportPath;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        $this->claimCodeceptionLoaded();
        $reportPath = $this->getReportPath();

        if (!@file_exists($reportPath)) {
            throw new RuntimeException(
                'The reportfile "failedTests.txt" did not exists.'
            );
        }

        $this->splitToGroupFiles(
            $this->filter(
                explode(
                    PHP_EOL,
                    file_get_contents($reportPath)
                )
            )
        );
    }

    /**
     * @param string $reportPath
     * @return FailedTestSplitterTask
     */
    public function setReportPath(string $reportPath): FailedTestSplitterTask
    {
        if (empty($reportPath)) {
            throw new \InvalidArgumentException('The reportPath could not be empty!');
        }

        $this->reportPath = $reportPath;

        return $this;
    }
}
