<?php

class MergeJUnitReportsTest extends \PHPUnit_Framework_TestCase
{
    use Codeception\Task\MergeReports;

    public function testMergeReports()
    {
        $task = new Codeception\Task\MergeXmlReportsTask;
        $task->from('tests/fixtures/result1.xml')
            ->from('tests/fixtures/result2.xml')
            ->into('tests/result/merged.xml')
            ->run();

        $this->assertFileExists('tests/result/merged.xml');
        $xml = file_get_contents('tests/result/merged.xml');
        $this->assertContains('<testsuite name="cli" tests="53" assertions="209" failures="0" errors="0"', $xml);
        $this->assertContains('<testsuite name="unit" tests="22" assertions="52"', $xml);
        $this->assertContains('<testcase file="/home/davert/Codeception/tests/cli/BootstrapCest.php"', $xml, 'from first file');
        $this->assertContains('<testcase name="testBasic" class="GenerateCestTest"', $xml, 'from second file');
    }

    public function setUp()
    {
        @mkdir('tests/result');
        @unlink('tests/result/merged.xml');
    }
}
