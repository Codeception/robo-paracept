<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use Codeception\Lib\Di;
use Codeception\Test\Cest;
use Codeception\Test\Descriptor as TestDescriptor;
use Codeception\Test\Loader as TestLoader;
use Exception;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionObject;
use Robo\Result;

/**
 * Loads all tests into groups and saves them to groupfile according to pattern.
 * The loaded tests can be filtered by the given Filter. FIFO principal
 *
 * ``` php
 * <?php
 * $this->taskSplitTestsByGroups(5)
 *    ->testsFrom('tests')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->addFilter(new Filter1())
 *    ->addFilter(new Filter2())
 *    ->run();
 * ```
 *
 * @see \Tests\Codeception\Task\Splitter\TestsSplitterTaskTest
 */
class TestsSplitterTask extends TestsSplitter
{
    /**
     * @throws \Robo\Exception\TaskException
     */
    public function run(): Result
    {
        $this->claimCodeceptionLoaded();
        $tests = $this->filter($this->loadTests());
        $numTests = count($tests);
        $this->printTaskInfo("Processing $numTests tests");

        $testsHaveAtLeastOneDependency = false;

        // test preloading (and fetching dependencies) requires dummy DI service.
        $di = new Di();
        // gather test dependencies and deal with dataproviders
        $testsListWithDependencies = [];
        foreach ($tests as $test) {
            if ($test instanceof DataProviderTestSuite) {
                $test = current($test->tests());
            }

            if ($test instanceof Cest) {
                $test->getMetadata()->setServices(['di' => $di]);
                $test->preload();
            }

            if (method_exists($test, 'getMetadata')) {
                $dependencies = $test->getMetadata()->getDependencies();
                if (count($dependencies) !== 0) {
                    $testsHaveAtLeastOneDependency = true;
                    $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = $dependencies;
                } else {
                    $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = [];
                }
            // little hack to get dependencies from phpunit test cases that are private.
            } elseif ($test instanceof TestCase) {
                $ref = new ReflectionObject($test);
                do {
                    try {
                        $property = $ref->getProperty('dependencies');
                        $property->setAccessible(true);
                        $dependencies = $property->getValue($test);
                        if (count($dependencies) !== 0) {
                            $testsHaveAtLeastOneDependency = true;
                            $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = $dependencies;
                        } else {
                            $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = [];
                        }
                    } catch (ReflectionException $exception) {
                        // go up on level on inheritance chain.
                    }
                } while ($ref = $ref->getParentClass());
            } else {
                $testsListWithDependencies[TestDescriptor::getTestFullName($test)] = [];
            }
        }

        if ($testsHaveAtLeastOneDependency) {
            $this->printTaskInfo('Resolving test dependencies');
            // make sure that dependencies are in array as full names
            try {
                $testsListWithDependencies = $this->resolveDependenciesToFullNames(
                    $testsListWithDependencies
                );
            } catch (Exception $e) {
                $this->printTaskError($e->getMessage());
                return Result::error($this, $e->getMessage(), ['exception' => $e]);
            }

            // resolved and ordered list of dependencies
            $orderedListOfTests = [];
            // helper array
            $unresolved = [];

            // Resolve dependencies for each test
            foreach (array_keys($testsListWithDependencies) as $test) {
                try {
                    [$orderedListOfTests, $unresolved] = $this->resolveDependencies(
                        $test,
                        $testsListWithDependencies,
                        $orderedListOfTests,
                        $unresolved
                    );
                } catch (Exception $e) {
                    $this->printTaskError($e->getMessage());
                    return Result::error($this, $e->getMessage(), ['exception' => $e]);
                }
            }

            // if we don't have any dependencies just use keys from original list.
        } else {
            $orderedListOfTests = array_keys($testsListWithDependencies);
        }

        // for even split, calculate number of tests in each group
        $numberOfElementsInGroup = round(count($orderedListOfTests) / $this->numGroups);
        $i = 1;
        $groups = [];

        // split tests into files.
        foreach ($orderedListOfTests as $test) {
            // move to the next group ONLY if number of tests is equal or greater desired number of tests in group
            // AND current test has no dependencies AKA: we  are in different branch than previous test
            if (
                !empty($groups[$i])
                && empty($testsListWithDependencies[$test])
                && $i <= ($this->numGroups - 1)
                && count($groups[$i]) >= $numberOfElementsInGroup
            ) {
                ++$i;
            }

            $groups[$i][] = $test;
        }

        $filenames = [];
        // saving group files
        foreach ($groups as $i => $groupTests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing $filename");
            file_put_contents($filename, implode("\n", $groupTests));
            $filenames[] = $filename;
        }
        $numFiles = count($filenames);

        return Result::success($this, "Split $numTests into $numFiles group files", [
            'groups' => $groups,
            'tests' => $tests,
            'files' => $filenames,
        ]);
    }

    protected function getTestLoader(): TestLoader
    {
        return new TestLoader(['path' => $this->testsFrom]);
    }

    protected function loadTests(): array
    {
        $testLoader = $this->getTestLoader();
        $testLoader->loadTests($this->testsFrom);

        return $testLoader->getTests();
    }
}
