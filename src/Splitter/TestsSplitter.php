<?php
declare(strict_types = 1);

namespace Codeception\Task\Splitter;

use Robo\Task\BaseTask;

abstract class TestsSplitter extends BaseTask
{
    /** @var int */
    protected $numGroups;
    /** @var string */
    protected $projectRoot = '.';
    /** @var string */
    protected $testsFrom = 'tests';
    /** @var string */
    protected $saveTo = 'tests/_data/paracept_';
    /** @var string */
    protected $excludePath = 'vendor';

    /**
     * TestsSplitter constructor.
     * @param int $groups number of groups to use
     */
    public function __construct(int $groups)
    {
        $this->numGroups = $groups;
    }

    public function projectRoot(string $path): TestsSplitter
    {
        $this->projectRoot = $path;

        return $this;
    }

    public function testsFrom(string $path): TestsSplitter
    {
        $this->testsFrom = $path;

        return $this;
    }

    public function groupsTo(string $pattern): TestsSplitter
    {
        $this->saveTo = $pattern;

        return $this;
    }

    public function excludePath(string $path): TestsSplitter
    {
        $this->excludePath = $path;

        return $this;
    }

    /**
     * @param       $item
     * @param array $items
     * @param array $resolved
     * @param array $unresolved
     *
     * @return array
     */
    protected function resolveDependencies(
        $item,
        array $items,
        array $resolved,
        array $unresolved
    ): array {
        $unresolved[] = $item;
        foreach ($items[$item] as $dep) {
            if (!in_array($dep, $resolved, true)) {
                if (!in_array($dep, $unresolved, true)) {
                    $unresolved[] = $dep;
                    [$resolved, $unresolved] =
                        $this->resolveDependencies($dep, $items, $resolved, $unresolved);
                } else {
                    throw new \RuntimeException("Circular dependency: $item -> $dep");
                }
            }
        }
        // Add $item to $resolved if it's not already there
        if (!in_array($item, $resolved, true)) {
            $resolved[] = $item;
        }
        // Remove all occurrences of $item in $unresolved
        while (($index = array_search($item, $unresolved, true)) !== false) {
            unset($unresolved[$index]);
        }

        return [$resolved, $unresolved];
    }

    /**
     * Make sure that tests are in array are always with full path and name.
     *
     * @param array $testsListWithDependencies
     *
     * @return array
     */
    protected function resolveDependenciesToFullNames(array $testsListWithDependencies): array
    {
        // make sure that dependencies are in array as full names
        foreach ($testsListWithDependencies as $testName => $test) {
            foreach ($test as $i => $dependency) {
                // sometimes it is written as class::method.
                // for that reason we do trim in first case and replace from :: to one in second case

                // just test name, that means that class name is the same, just different method name
                if (strrpos($dependency, ':') === false) {
                    $testsListWithDependencies[$testName][$i] = trim(
                        substr($testName, 0, strrpos($testName, ':')),
                        ':'
                    ) . ':' . $dependency;
                    continue;
                }
                $dependency = str_replace('::', ':', $dependency);
                // className:testName, that means we need to find proper test.
                [$targetTestFileName, $targetTestMethodName] = explode(':', $dependency);

                // look for proper test in list of all tests. Test could be in different directory
                // so we need to compare strings and if matched we just assign found test name
                foreach (array_keys($testsListWithDependencies) as $arrayKey) {
                    if (str_contains(
                        $arrayKey,
                        $targetTestFileName . '.php:' . $targetTestMethodName
                    )) {
                        $testsListWithDependencies[$testName][$i] = $arrayKey;
                        continue 2;
                    }
                }
                throw new \RuntimeException(
                    'Dependency target test ' . $dependency . ' not found.'
                    . 'Please make sure test exists and you are using full test name'
                );
            }
        }

        return $testsListWithDependencies;
    }
}
