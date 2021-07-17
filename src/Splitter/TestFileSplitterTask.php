<?php
declare(strict_types = 1);

namespace Codeception\Task\Splitter;

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
 * ?>
 * ```
 */
class TestFileSplitterTask extends TestsSplitter
{
    private $pattern = ['*Cept.php', '*Cest.php', '*Test.php', '*.feature'];

    public function run()
    {
        $files = Finder::create()
            ->followLinks()
            ->name($this->getPattern())
            ->path($this->testsFrom)
            ->in($this->projectRoot ?: getcwd())
            ->exclude($this->excludePath);

        $i = 0;
        $groups = [];

        $this->printTaskInfo('Processing ' . count($files) . ' files');
        $files = $this->filter(iterator_to_array($files->getIterator()));

        // splitting tests by groups
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $groups[($i % $this->numGroups) + 1][] = $file->getRelativePathname();
            $i++;
        }

        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing $filename");
            file_put_contents($filename, implode("\n", $tests));
        }
    }

    /**
     * @param string[] $pattern
     * @return TestFileSplitterTask
     */
    public function setPattern(array $pattern): TestFileSplitterTask
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * @param string $pattern
     * @return TestFileSplitterTask
     */
    public function addPattern(string $pattern): TestFileSplitterTask
    {
        $this->pattern[] = $pattern;

        return $this;
    }

    public function getPattern(): array
    {
        return $this->pattern;
    }
}
