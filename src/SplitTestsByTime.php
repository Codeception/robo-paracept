<?php

namespace StripChat\Unit;

use Codeception\Task\TestsSplitter;
use PHPUnit_Framework_TestSuite_DataProvider;
use Robo\Contract\TaskInterface;
use Robo\Exception\TaskException;

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
        foreach ($groups as $i => list('tests' => $tests, 'sum' => $sum)) {
            $filename = $this->saveTo . ($i + 1);
            $this->printTaskInfo("Writing $filename: " . count($tests) . ' tests with ' . number_format($sum, 2) . ' seconds');
            file_put_contents($filename, implode("\n", $tests));
        }
    }

    /**
     * Find group num with min execute time
     *
     * @param $groups
     * @return int|string
     */
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
