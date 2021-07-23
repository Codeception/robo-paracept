<?php

namespace Codeception\Task\Splitter;

use Codeception\Configuration;
use Codeception\Test\Descriptor;
use Codeception\Test\Loader;
use JsonException;
use PHPUnit\Framework\DataProviderTestSuite;
use Robo\Exception\TaskException;
use RuntimeException;

/**
 * This task will not consider any 'depends' annotation!
 * It will only split tests by the execution time
 */
class SplitTestsByFailed extends TestsSplitter
{
    protected $failedReportFile = 'tests/_output/failed';

    public function failedReportFile(string $path): self
    {
        $this->failedReportFile = $path;

        return $this;
    }

    public function run(): void
    {
        $this->claimCodeceptionLoaded();

        if (!is_file($this->failedReportFile)) {
            throw new TaskException($this, 'Can not find failed report file - no test have failed');
        }

        $failedFiles = file_get_contents($this->failedReportFile);
        $failedFiles = trim($failedFiles, "\n");
        $failedFiles = explode("\n", $failedFiles);

        $i = 0;
        $groups = [];

        $this->printTaskInfo('Rerun ' . count($failedFiles) . ' failed files');
        /** @var SplFileInfo $file */
        foreach ($failedFiles as $file) {
            $groups[($i % $this->numGroups) + 1][] = $file;
            $i++;
        }

        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing $filename: " . count($tests) . ' tests');
            file_put_contents($filename, implode("\n", $tests));
        }
    }
}
