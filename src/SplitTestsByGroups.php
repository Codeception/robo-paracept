<?php
namespace Codeception\Task;

use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait SplitTestsByGroups
{
    protected function taskSplitTestsByTime($numGroups)
    {
        return $this->task(SplitTestsByTimeTask::class, $numGroups);
    }

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
 * $this->taskSplitTestsByTime(5)
 *    ->testsFrom('tests')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->run();
 * ?>
 * ```
 */
class SplitTestsByTimeTask extends TestsSplitter implements TaskInterface
{
    protected $statFile = 'tests/_output/timeReport.json';

    public function statFile($path)
    {
        $this->statFile = $path;

        return $this;
    }

    public function run()
    {
        if (!class_exists('\Codeception\Test\Loader')) {
            throw new TaskException($this, 'This task requires Codeception to be loaded. Please require autoload.php of Codeception');
        }
        if (!is_file($this->statFile)) {
            throw new TaskException($this, 'Can not find stat file - run tests with TimeReporter extension');
        }

        $testLoader = new \Codeception\Test\Loader(['path' => $this->testsFrom]);
        $testLoader->loadTests($this->testsFrom);
        $tests = $testLoader->getTests();

        $data = file_get_contents($this->statFile);
        $data = json_decode($data, true);

        $testsWithTime = [];
        $groups = [];

        $this->printTaskInfo('Processing ' . count($tests) . ' tests');
        foreach ($tests as $test) {
            if ($test instanceof PHPUnit_Framework_TestSuite_DataProvider) {
                $test = current($test->tests());
            }
            $testName = \Codeception\Test\Descriptor::getTestFullName($test);
            $testsWithTime[$testName] = $data[$testName];
        }

        arsort($testsWithTime);

        for ($i = 0; $i < $this->numGroups; $i++) {
            $groups[$i] = [
                'tests' => [],
                'sum' => 0,
            ];
        }

        foreach ($testsWithTime as $test => $time) {
            $i = $this->getMinGroup($groups);
            $groups[$i]['tests'][] = $test;
            $groups[$i]['sum'] += $time;
        }

        // saving group files
        foreach ($groups as $i => list('tests' => $tests, 'sum' => $sum)) {
            $filename = $this->saveTo . ($i + 1);
            $this->printTaskInfo("Writing $filename: " . count($tests) . ' tests with ' . number_format($sum, 2) . ' seconds');
            file_put_contents($filename, implode("\n", $tests));
        }
    }

    protected function getMinGroup($groups)
    {
        $min = 0;
        $minSum = $groups[0]['sum'];
        foreach ($groups as $i => $data) {
            if ($data['sum'] < $minSum) {
                $min = $i;
                $minSum = $data['sum'];
            }
        }
        return $min;
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
