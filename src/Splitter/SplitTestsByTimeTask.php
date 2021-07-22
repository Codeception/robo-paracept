<?php

namespace Codeception\Task\Splitter;

use Codeception\Configuration;
use Codeception\Test\Descriptor;
use Codeception\Test\Loader;
use JsonException;
use PHPUnit\Framework\DataProviderTestSuite;
use Robo\Exception\TaskException;
use RuntimeException;

/**
 * This task will not consider any 'depends' annotation!
 * It will only split tests by the execution time
 */
class SplitTestsByTimeTask extends TestsSplitter
{
    protected $statFile = 'tests/_output/timeReport.json';

    public function statFile(string $path): self
    {
        $this->statFile = $path;

        return $this;
    }

    public function run(): void
    {
        $this->claimCodeceptionLoaded();

        if (!is_file($this->statFile)) {
            throw new TaskException($this, 'Can not find stat file - run tests with TimeReporter extension');
        }

        $testLoader = new Loader(['path' => $this->testsFrom]);
        $testLoader->loadTests($this->testsFrom);
        $tests = $testLoader->getTests();
        $data = $this->readStatFileContent();

        $testsWithTime = [];
        $groups = [];

        $this->printTaskInfo('Processing ' . count($tests) . ' tests');
        foreach ($tests as $test) {
            if ($test instanceof DataProviderTestSuite) {
                $test = current($test->tests());
            }
            $testName = Descriptor::getTestFullName($test);
            if (1 !== preg_match('~^/~', $testName)) {
                $testName = '/' . $testName;
            }

            $testName = substr(str_replace($this->getProjectDir(), '', $testName), 1);
            $testsWithTime[$testName] = $data[$testName] ?? 0;
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
        foreach ($groups as $i => ['tests' => $tests, 'sum' => $sum]) {
            $filename = $this->saveTo . ($i + 1);
            $this->printTaskInfo(
                sprintf(
                    'Writing %s: %d tests with %01.2f seconds',
                    $filename,
                    count($tests),
                    number_format($sum, 2)
                )
            );
            file_put_contents($filename, implode("\n", $tests));
        }
    }

    /**
     * Find group num with min execute time
     *
     * @param array $groups
     * @return int
     */
    protected function getMinGroup(array $groups): int
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

    /**
     * @return array
     */
    private function readStatFileContent(): array
    {
        if (false === ($data = file_get_contents($this->statFile))) {
            throw new RuntimeException('Could not read content of stat file.');
        }

        try {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                "Could not decode content of stat file.",
                0,
                $exception
            );
        }

        return $data;
    }

    public function getProjectDir(): string
    {
        return Configuration::projectDir();
    }
}
