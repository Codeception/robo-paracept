<?php
namespace Codeception\Task;

use Codeception\Lib\GroupManager;
use Codeception\Test\Cept;
use Codeception\Test\Cest;
use Codeception\Util\Annotation;
use Robo\Common\TaskIO;
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
    use TaskIO;

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
    private $filterGroup = '';

    /**
     * Matches any line that this a line comment beginning with "//".
     */
    const REGEXP_LINE_COMMENT = '~\/\/(.*?)$~m';

    /**
     * Matches any line that is part of a block comment beginning with "/*".
     */
    const REGEXP_BLOCK_COMMENT = '~\/*\*(.*?)\*\/~ms';

    /**
     * Sets a filter group. Only tests belonging to this specific Codeception group will be executed.
     *
     * @param $group string name of the Codeception group to be filtered
     * @return $this
     */
    public function filterByGroup($group)
    {
        $this->filterGroup = $group;
        return $this;
    }

    public function run()
    {
        if (!class_exists('\Codeception\Test\Loader')) {
            throw new TaskException($this, "This task requires Codeception to be loaded. Please require autoload.php of Codeception");
        }
        $testLoader = new \Codeception\Test\Loader(['path' => $this->testsFrom]);
        $testLoader->loadTests($this->testsFrom);
        $tests = $testLoader->getTests();

        $i = 0;
        $groups = [];

        $this->printTaskInfo("Processing ".count($tests)." tests");
        // splitting tests by groups
        foreach ($tests as $test) {
            if (empty($this->filterGroup) || $this->matchesToFilterGroup($test)) {
                $fullname = str_replace('\\', '/', \Codeception\Test\Descriptor::getTestFullName($test));
                $groups[($i % $this->numGroups) + 1][] = $fullname;
                $i++;
            }
        }

        $this->printTaskInfo(
            sprintf(
                "%s test%s match%s the criterias. They will be divided in up to %s groups.",
                $i,
                ($i == 1 ? '' : 's'),
                ($i == 1 ? 'es' : ''),
                $this->numGroups
            )
        );


        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo(sprintf("Writing %s (includes %s tests)", $filename, count($tests)));
            file_put_contents($filename, implode("\n", $tests));
        }
    }

    /**
     * Determines if the test is part of the filter group.
     *
     * @param $test mixed Any kind of codeption test. Cest or Cept
     * @return bool Returns "true" if the test is part of the filter group, otherwise "false".
     */
    protected function matchesToFilterGroup($test)
    {
        $isFilterGroupMatched = false;

        if (false === empty($this->filterGroup)) {
            $phpunitGroups = [];

            if ($test instanceof Cept) {
                $phpunitGroups = Annotation::fetchAllFromComment(
                    'group',
                    $this->findCommentsInCept($test->getSourceCode())
                );
            } elseif ($test instanceof Cest) {
                $groupManager = new GroupManager(array());
                $phpunitGroups = $groupManager->groupsForTest($test);
            }

            $isFilterGroupMatched = in_array($this->filterGroup, $phpunitGroups);
        }

        return $isFilterGroupMatched;
    }

    /**
     * Returns all comments that could be found in the code of a Cept test
     *
     * @param $code string File content of a Cept file
     * @return string Comments as a string
     */
    protected function findCommentsInCept($code)
    {
        $matches = [];
        $comments = '';
        $hasLineComment = preg_match_all(self::REGEXP_LINE_COMMENT, $code, $matches);
        if ($hasLineComment && isset($matches[1])) {
            foreach ($matches[1] as $line) {
                $comments .= $line . PHP_EOL;
            }
        }
        $hasBlockComment = preg_match(self::REGEXP_BLOCK_COMMENT, $code, $matches);
        if ($hasBlockComment && isset($matches[1])) {
            $comments .= $matches[1] . PHP_EOL;
        }
        return $comments;
    }
}

/**
 * Finds all test files and splits them by group.
 * Unlike `SplitTestsByGroupsTask` does not load them into memory and not requires Codeception to be loaded
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
            ->name("*Cept.php")
            ->name("*Cest.php")
            ->name("*Test.php")
            ->path($this->testsFrom)
            ->in($this->projectRoot ? $this->projectRoot : getcwd());

        $i = 0;
        $groups = [];

        $this->printTaskInfo("Processing ".count($files)." files");
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
