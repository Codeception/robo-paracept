<?php
namespace Codeception\Task;

use Codeception\Test\Descriptor as TestDescriptor;
use Codeception\Test\Loader as TestLoader;
use \PHPUnit\Framework\DataProviderTestSuite as DataProviderTestSuite;
use \PHPUnit_Framework_TestSuite_DataProvider as DataProvider;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
        $testLoader = new TestLoader(['path' => $this->testsFrom]);
        $testLoader->loadTests($this->testsFrom);
        $tests = $testLoader->getTests();

        $this->printTaskInfo('Processing ' . count($tests) . ' tests');

        $testsHaveAtLeastOneDependency = false;

        // test preloading (and fetching dependencies) requires dummy DI service.
        $di = new \Codeception\Lib\Di();
        // gather test dependencies and deal with dataproviders
        $testsListWithDependencies = [];
        foreach ($tests as $test) {
            if ($test instanceof DataProvider || $test instanceof DataProviderTestSuite) {
                $test = current($test->tests());
            }

            // load dependencies for cest type. Unit tests dependencies are loaded automatically
            if ($test instanceof \Codeception\Test\Cest) {
                $test->getMetadata()->setServices(['di'=>$di]);
                $test->preload();
            }

            if (method_exists($test, 'getMetadata')) {
                $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = $test->getMetadata()
                                                                                          ->getDependencies();
                if ($testsHaveAtLeastOneDependency === false and count($test->getMetadata()->getDependencies()) != 0) {
                    $testsHaveAtLeastOneDependency = true;
                }

            // little hack to get dependencies from phpunit test cases that are private.
            } elseif ($test instanceof \PHPUnit\Framework\TestCase) {
                $ref = new \ReflectionObject($test);
                do {
                    try{
                        $property = $ref->getProperty('dependencies');
                        $property->setAccessible(true);
                        $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = $property->getValue($test);

                        if ($testsHaveAtLeastOneDependency === false and count($property->getValue($test)) != 0) {
                            $testsHaveAtLeastOneDependency = true;
                        }

                    } catch (\ReflectionException $e) {
                        // go up on level on inheritance chain.
                    }
                } while($ref = $ref->getParentClass());

            } else {
                $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = [];
            }
        }

        if ($testsHaveAtLeastOneDependency) {
            $this->printTaskInfo('Resolving test dependencies');

            // make sure that dependencies are in array as full names
            try {
                $testsListWithDependencies = $this->resolveDependenciesToFullNames($testsListWithDependencies);
            } catch (\Exception $e) {
                $this->printTaskError($e->getMessage());
                return false;
            }

            // resolved and ordered list of dependencies
            $orderedListOfTests = [];
            // helper array
            $unresolved = [];

            // Resolve dependencies for each test
            foreach (array_keys($testsListWithDependencies) as $test) {
                try {
                    list ($orderedListOfTests, $unresolved) = $this->resolveDependencies($test, $testsListWithDependencies, $orderedListOfTests, $unresolved);
                } catch (\Exception $e) {
                    $this->printTaskError($e->getMessage());
                    return false;
                }
            }

        // if we don't have any dependencies just use keys from original list.
        } else {
            $orderedListOfTests = array_keys($testsListWithDependencies);
        }

        // for even split, calculate number of tests in each group
        $numberOfElementsInGroup = floor(count($orderedListOfTests) / $this->numGroups);

        $i = 1;
        $groups = [];

        // split tests into files.
        foreach ($orderedListOfTests as $test) {
            // move to the next group ONLY if number of tests is equal or greater desired number of tests in group
            // AND current test has no dependencies AKA: we  are in different branch than previous test
            if (!empty($groups[$i]) AND count($groups[$i]) >= $numberOfElementsInGroup AND $i <= ($this->numGroups-1) AND empty($testsListWithDependencies[$test])) {
                $i++;
            }

            $groups[$i][] = $test;
        }

        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing $filename");
            file_put_contents($filename, implode("\n", $tests));
        }
    }
}
