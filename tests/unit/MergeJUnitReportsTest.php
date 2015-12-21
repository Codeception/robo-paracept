<?php

class MergeJUnitReportsTest extends \Codeception\TestCase\Test
{
    use \Codeception\Task\MergeReports;

    public function testMergeReports()
    {
        $this->taskMergeXmlReports()
            ->from('tests/unit/fixtures/result1.xml')
            ->from('tests/unit/fixtures/result2.xml')
            ->into('tests/unit/result/merged.xml')
            ->run();

        $this->assertFileExists('tests/unit/result/merged.xml');
        $xml = file_get_contents('tests/unit/result/merged.xml');
        $this->assertContains('<testsuite name="cli" tests="53" assertions="209" failures="0" errors="0"', $xml);
        $this->assertContains('<testsuite name="unit" tests="22" assertions="52"', $xml);
        $this->assertContains('<testcase file="/home/davert/Codeception/tests/cli/BootstrapCest.php"', $xml, 'from first file');
        $this->assertContains('<testcase name="testBasic" class="GenerateCestTest"', $xml, 'from second file');
    }

    public function setUp()
    {
        @mkdir('tests/unit/result');
        @unlink('tests/unit/result/merged.xml');
    }
}
