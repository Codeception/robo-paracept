<?php
namespace Codeception\Task;

use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait SplitTestsByGroups
{
    protected function taskSplitTestsByGroups($numGroups)
    {
        return $this->task(SplitTestsByGroupsTask::class, $numGroups);
    }

    protected function taskSplitTestFilesByGroups($numGroups)
    {
        return $this->task(SplitTestFilesByGroupsTask::class, $numGroups);
    }
}

abstract class TestsSplitter extends BaseTask
{
    protected $numGroups;
    protected $projectRoot = '.';
    protected $testsFrom = 'tests';
    protected $saveTo = 'tests/_data/paracept_';

    public function __construct($groups)
    {
        $this->numGroups = $groups;
    }

    public function projectRoot($path)
    {
        $this->projectRoot = $path;

        return $this;
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
        if (!class_exists('\Codeception\Test\Loader')) {
            throw new TaskException($this, 'This task requires Codeception to be loaded. Please require autoload.php of Codeception');
        }
        $testLoader = new \Codeception\Test\Loader(['path' => $this->testsFrom]);
        $testLoader->loadTests($this->testsFrom);
        $tests = $testLoader->getTests();

        $i = 0;
        $groups = [];

        $this->printTaskInfo('Processing ' . count($tests) . ' tests');
        // splitting tests by groups
        foreach ($tests as $test) {
            if ($test instanceof PHPUnit_Framework_TestSuite_DataProvider) {
                $test = current($test->tests());
            }
            $groups[($i % $this->numGroups) + 1][] = \Codeception\Test\Descriptor::getTestFullName($test);
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
 * Unlike `SplitTestsByGroupsTask` does not load them into memory and not requires Codeception to be loaded.
 *
 * ``` php
 * <?php
 * $this->taskSplitTestFilesByGroups(5)
 *    ->testsFrom('tests/unit/Acme')
 *    ->codeceptionRoot('projects/tested')
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
            ->name('*Cept.php')
            ->name('*Cest.php')
            ->name('*Test.php')
            ->name('*.feature')
            ->path($this->testsFrom)
            ->in($this->projectRoot ? $this->projectRoot : getcwd());

        $i = 0;
        $groups = [];

        $this->printTaskInfo('Processing ' . count($files) . ' files');
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
}
