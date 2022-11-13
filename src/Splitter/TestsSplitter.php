<?php

declare(strict_types=1);

namespace Codeception\Task\Splitter;

use Codeception\Configuration;
use Codeception\Task\Filter\DefaultFilter;
use Codeception\Task\Filter\Filter;
use ReflectionClass;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use RuntimeException;

abstract class TestsSplitter extends BaseTask
{
    protected int $numGroups;

    protected string $projectRoot = '.';

    /** @var string[]|string */
    protected $testsFrom = 'tests';

    protected string $saveTo = 'tests/_data/paracept_';

    protected string $excludePath = 'vendor';

    /** @var Filter[] $filter */
    protected array $filter = [];

    /**
     * TestsSplitter constructor.
     *
     * @param int $groups number of groups to use
     */
    public function __construct(int $groups)
    {
        $this->numGroups = $groups;
        $this->filter[] = new DefaultFilter();
    }

    public function addFilter(Filter $filter): TestsSplitter
    {
        if (!in_array($filter, $this->filter, true)) {
            $this->filter[] = $filter;
        }

        return $this;
    }

    public function getProjectRoot(): string
    {
        return realpath($this->projectRoot);
    }

    public function projectRoot(string $path): TestsSplitter
    {
        $this->projectRoot = $path;

        return $this;
    }

    /**
     * @param string[]|string $path - a single path or array of paths
     * @return $this|TestsSplitter
     */
    public function testsFrom($path): TestsSplitter
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
     * @param mixed $item
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
                    throw new RuntimeException(sprintf('Circular dependency: %s -> %s', $item, $dep));
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
     */
    protected function resolveDependenciesToFullNames(array $testsListWithDependencies): array
    {
        // make sure that dependencies are in array as full names
        foreach ($testsListWithDependencies as $testName => $test) {
            foreach ($test as $i => $dependency) {
                if (is_a($dependency, '\PHPUnit\Framework\ExecutionOrderDependency')) {
                    // getTarget gives the classname::method
                    $dependency = $dependency->getTarget();
                    [$class, $method] = explode('::', $dependency);
                    $ref = new ReflectionClass($class);
                    $dependency = $ref->getFileName() . ':' . $method;
                }

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
                if (false === strrpos($targetTestFileName, '.php')) {
                    $targetTestFileName .= '.php';
                }

                // look for proper test in list of all tests. Test could be in different directory
                // so we need to compare strings and if matched we just assign found test name
                foreach (array_keys($testsListWithDependencies) as $arrayKey) {
                    if (
                        str_contains(
                            $arrayKey,
                            $targetTestFileName . ':' . $targetTestMethodName
                        )
                    ) {
                        $testsListWithDependencies[$testName][$i] = $arrayKey;
                        continue 2;
                    }
                }

                throw new RuntimeException(
                    'Dependency target test ' . $dependency . ' not found.'
                    . 'Please make sure test exists and you are using full test name'
                );
            }
        }

        return $testsListWithDependencies;
    }

    /**
     * Filter tests by the given filters, FIFO principal
     */
    protected function filter(array $tests): array
    {
        foreach ($this->filter as $filter) {
            $filter->setTests($tests);
            $tests = $filter->filter();
        }

        return $tests;
    }

    /**
     * Claims that the Codeception is loaded for Tasks which need it
     *
     * @throws TaskException
     */
    protected function claimCodeceptionLoaded(): void
    {
        if (!$this->doCodeceptLoaderExists()) {
            throw new TaskException(
                $this,
                'This task requires Codeception to be loaded. Please require autoload.php of Codeception'
            );
        }

        try {
            // load Codeception config to set base directory
            \Codeception\Configuration::config();
        } catch (\Exception $e) {
            $this->output()->writeln('Codeception config was not loaded, please load it manually');
        }
    }

    protected function doCodeceptLoaderExists(): bool
    {
        return class_exists(\Codeception\Test\Loader::class);
    }

    /**
     * Splitting array of files to the group files
     *
     * @param string[] $files - the relative path of the Testfile with or without test function
     * @example $this->splitToGroupFiles(['tests/FooCest.php', 'tests/BarTest.php:testBarReturn']);
     */
    protected function splitToGroupFiles(array $files): array
    {
        $i = 0;
        $groups = [];

        if (!Configuration::projectDir()) {
            $this->output()->writeln("Codeception config was not loaded, paths to tests may not be set correctly.");
            $this->output()->writeln("Execute \Codeception\Configuration::config() before this task");
        }


        $this->printTaskInfo('Processing ' . count($files) . ' files');

        // splitting tests by groups
        /** @var string $file */
        foreach ($files as $file) {
            $groups[($i % $this->numGroups) + 1][] = $file;
            ++$i;
        }

        $filenames = [];
        // saving group files
        foreach ($groups as $i => $tests) {
            $filename = $this->saveTo . $i;
            $this->printTaskInfo("Writing {$filename}");
            file_put_contents($filename, implode("\n", $tests));
            $filenames[] = $filename;
        }
        return $filenames;
    }
}
