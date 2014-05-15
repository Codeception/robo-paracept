<?php
namespace Codeception\Task;

use Robo\Task\Shared\TaskException;
use Robo\Task\Shared\TaskInterface;
use Symfony\Component\Finder\Finder;

trait SplitTestsByGroups {

    protected function taskSplitTestsByGroups($numGroups)
    {
        return new SplitTestsByGroupsTask($numGroups);
    }

    protected function taskSplitTestFilesByGroups($numGroups)
    {
        return new SplitTestFilesByGroupsTask($numGroups);
    }
    
}

abstract class TestsSplitter {
    use \Robo\Output;

    protected $numGroups;
    protected $testsFrom = 'tests';
    protected $saveTo = 'tests/_log/paracept_';

    public function __construct($groups)
    {
        $this->numGroups = $groups;
    }

    public function testsFrom($path)
    {
        $this->testsFrom = $path;
        return $this;
    }

    public function groupsTo($pattern)
    {
        $this->saveTo = $pattern;
        return $this;
    }

}

/**
 *
 * Loads all tests into groups and saves them to groupfile according to pattern.
 *
 * ``` php
 * <?php
 * $this->taskSplitTestsByGroups(5)
 *    ->testsFrom('tests')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->run();
 * ?>
 * ```
 */
class SplitTestsByGroupsTask extends TestsSplitter implements TaskInterface
{
    public function run()
    {
        if (!class_exists('\Codeception\TestLoader')) {
            throw new TaskException($this, "This task requires Codeception to be loaded. Please require autoload.php of Codeception");
        }
        $testLoader = new \Codeception\TestLoader($this->testsFrom);
        $testLoader->loadTests();
        $tests = $testLoader->getTests();

        $i = 0;
        $groups = [];

        $this->printTaskInfo("Processing ".count($tests)." files");
        // splitting tests by groups
        foreach ($tests as $test) {
            $groups[($i % $this->numGroups) + 1][] = \Codeception\TestCase::getTestFullName($test);
            $i++;
        }

        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing $filename");
            file_put_contents($filename, implode("\n", $tests));
        }
    }
}

/**
 * Finds all test files and splits them by group.
 * Unlike `SplitTestsByGroupsTask` does not load them into memory and not requires Codeception to be loaded
 *
 * ``` php
 * <?php
 * $this->taskSplitTestFilesByGroups(5)
 *    ->testsFrom('tests')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->run();
 * ?>
 * ```
 */
class SplitTestFilesByGroupsTask extends TestsSplitter implements TaskInterface
{
    public function run()
    {
        $files = Finder::create()
            ->name("*Cept.php")
            ->name("*Cest.php")
            ->name("*Test.php")
            ->in($this->testsFrom);

        $i = 0;
        $groups = [];

        $this->printTaskInfo("Processing ".count($tests)." files");
        // splitting tests by groups
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
}