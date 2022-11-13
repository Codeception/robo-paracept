<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use Robo\Result;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Finds all test files and splits them into group.s
 * Unlike `TestsSplitterTask` does not load them into memory and not requires Codeception to be loaded.
 * Here you can also use your on Filter
 * Please be aware that we pass an array of SplFileInfo to the filter
 *
 * ``` php
 * <?php
 * $this->taskSplitTestFilesByGroups(5)
 *    ->testsFrom('tests/unit/Acme')
 *    ->codeceptionRoot('projects/tested')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->addFilter(new Filter1())
 *    ->addFilter(new Filter2())
 *    ->run();
 * ```
 *
 * @see \Tests\Codeception\Task\Splitter\TestFileSplitterTaskTest
 */
class TestFileSplitterTask extends TestsSplitter
{
    /**
     * @var string[]
     */
    private array $pattern = ['*Cept.php', '*Cest.php', '*Test.php', '*.feature'];

    public function run(): Result
    {
        $files = Finder::create()
            ->followLinks()
            ->name($this->getPattern())
            ->path($this->testsFrom)
            ->in($this->projectRoot ?: getcwd())
            ->exclude($this->excludePath);



        $filenames = $this->splitToGroupFiles(
            array_map(
                static fn(SplFileInfo $fileInfo): string => $fileInfo->getRelativePathname(),
                $this->filter(iterator_to_array($files->getIterator()))
            )
        );

        $numFiles = count($filenames);

        return Result::success($this, "Split all tests into $numFiles group files", [
            'files' => $filenames,
        ]);
    }

    /**
     * @param string[] $pattern
     */
    public function setPattern(array $pattern): self
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function addPattern(string $pattern): self
    {
        $this->pattern[] = $pattern;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPattern(): array
    {
        return $this->pattern;
    }
}
