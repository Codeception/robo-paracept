<?php
declare(strict_types = 1);

namespace Tests\Codeception\Task\Merger;

use Codeception\Task\Merger\XmlReportMergerTask;
use Consolidation\Log\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class XmlReportMergerTaskTest extends TestCase
{
    public function testMergeReports(): void
    {
        $task = new XmlReportMergerTask();
        $task->setLogger(new Logger(new NullOutput()));
        $task->from(TEST_PATH . '/fixtures/result1.xml')
            ->from(TEST_PATH . '/fixtures/result2.xml')
            ->into(TEST_PATH . '/result/merged.xml')
            ->run();

        $this->assertFileExists(TEST_PATH . '/result/merged.xml');
        $xml = file_get_contents(TEST_PATH . '/result/merged.xml');
        $this->assertStringContainsString(
            '<testsuite name="cli" tests="53" assertions="209" failures="0" errors="0"',
            $xml
        );
        $this->assertStringContainsString(
            '<testsuite name="unit" tests="22" assertions="52"',
            $xml
        );
        $this->assertStringContainsString(
            '<testcase file="/home/anywhere/Codeception/tests/cli/BootstrapCest.php"',
            $xml,
            'from first file'
        );
        $this->assertStringContainsString(
            '<testcase name="testBasic" class="GenerateCestTest"',
            $xml,
            'from second file'
        );
    }

    public function testMergeRewriteReports(): void
    {
        $task = new XmlReportMergerTask();
        $task->setLogger(new Logger(new NullOutput()));
        $task->from('tests/fixtures/result1.xml')
            ->from('tests/fixtures/result2.xml')
            ->into('tests/result/merged.xml')
            ->run();
        $this->assertFileExists('tests/result/merged.xml');

        $task->mergeRewrite()->run();
        $this->assertFileExists('tests/result/merged.xml');
        $xml = file_get_contents('tests/result/merged.xml');
        $this->assertStringContainsString('<testsuite name="cli" tests="51" assertions="204" failures="0" errors="0"', $xml);
        $this->assertStringContainsString('<testsuite name="unit" tests="22" assertions="52"', $xml);
        $this->assertStringContainsString('<testcase file="/home/anywhere/Codeception/tests/cli/BootstrapCest.php"', $xml, 'from first file');
        $this->assertStringContainsString('<testcase name="testBasic" class="GenerateCestTest"', $xml, 'from second file');
    }

}
