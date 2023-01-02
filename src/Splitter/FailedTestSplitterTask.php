<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use InvalidArgumentException;
use Robo\Result;
use RuntimeException;

/**
 * @see \Tests\Codeception\Task\Splitter\FailedTestSplitterTaskTest
 */
class FailedTestSplitterTask extends TestsSplitter
{
    private ?string $reportPath = null;

    /**
     * @return string - the absolute path to the report file with the failed tests
     */
    public function getReportPath(): ?string
    {
        return $this->reportPath;
    }

    /**
     * @inheritDoc
     */
    public function run(): Result
    {
        $this->claimCodeceptionLoaded();
        $reportPath = $this->getReportPath();

        if (!@file_exists($reportPath) || !is_file($reportPath)) {
            throw new RuntimeException(
                'The report file did not exists or is not a regular file.'
            );
        }

        $filenames = $this->splitToGroupFiles(
            $this->filter(
                explode(
                    PHP_EOL,
                    file_get_contents($reportPath)
                )
            )
        );

        $numFiles = count($filenames);

        return Result::success($this, "Split all tests into $numFiles group files", [
            'files' => $filenames,
        ]);
    }

    public function setReportPath(string $reportFilePath): self
    {
        if (empty($reportFilePath)) {
            throw new InvalidArgumentException('The reportPath could not be empty!');
        }

        $this->reportPath = $reportFilePath;

        return $this;
    }
}
