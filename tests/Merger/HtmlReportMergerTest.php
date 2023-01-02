<?php

declare(strict_types=1);

namespace Tests\Codeception\Task\Merger;

use Codeception\Task\Merger\HtmlReportMerger;
use Consolidation\Log\Logger;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use const Tests\Codeception\Task\TEST_PATH;

final class HtmlReportMergerTest extends TestCase
{
    /**
     * @covers ::run
     */
    public function testRun(): void
    {
        $expectedTimeInSeconds = 234.98;
        $expectedSuccess= 3;

        $reportPath = TEST_PATH . '/fixtures/reports/html/';
        $task = new HtmlReportMerger();
        $task->setLogger(new Logger(new NullOutput()));

        $resultReport = TEST_PATH . '/result/report.html';
        $task
            ->from(
                [
                    $reportPath . 'report_0.html', // this file did not exists and it should not fail
                    $reportPath . 'report_1.html',
                    $reportPath . 'report_2.html',
                    $reportPath . 'report_3.html',
                ]
            )
            ->into($resultReport)
            ->run();

        $this->assertFileExists($resultReport);

        //read first source file as main
        $dstHTML = new DOMDocument();
        $dstHTML->loadHTMLFile($resultReport, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        /** @var DOMNodeList $values */
        $values = (new DOMXPath($dstHTML))
            ->query("//*[contains(@class,'scenarioSuccessValue')]");

        $this->assertCount(1, $values);
        $this->assertSame($expectedSuccess, (int)$values[0]->nodeValue);

        $values = (new DOMXPath($dstHTML))
            ->query("//h1[text() = 'Codeception Results ']");
        preg_match(
            '#^Codeception Results .* \((?<timesum>\d+\.\d+)s\)$#',
            $values[0]->nodeValue,
            $matches
        );

        $this->assertSame($expectedTimeInSeconds, (float)$matches['timesum']);
    }

    /**
     * @covers ::run
     */
    public function testRunWithCodeception5Reports(): void
    {
        $expectedTimeInSeconds = '03:34.98';
        $expectedSuccess= 3;

        $reportPath = TEST_PATH . '/fixtures/reports/html/';
        $task = new HtmlReportMerger();
        $task->setLogger(new Logger(new NullOutput()));

        $resultReport = TEST_PATH . '/result/report_codeception5.html';
        $task
            ->from(
                [
                    $reportPath . 'report_0_codeception5.html', // this file did not exists and it should not fail
                    $reportPath . 'report_1_codeception5.html',
                    $reportPath . 'report_2_codeception5.html',
                    $reportPath . 'report_3_codeception5.html',
                ]
            )
            ->into($resultReport)
            ->run();

        $this->assertFileExists($resultReport);

        //read first source file as main
        $dstHTML = new DOMDocument();
        $dstHTML->loadHTMLFile($resultReport, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        /** @var DOMNodeList $values */
        $values = (new DOMXPath($dstHTML))
            ->query("//*[contains(@class,'scenarioSuccessValue')]");

        $this->assertCount(1, $values);
        $this->assertSame($expectedSuccess, (int)$values[0]->nodeValue);

        $values = (new DOMXPath($dstHTML))
            ->query("//h1[text() = 'Codeception Results ']");
        preg_match(
            '#^Codeception Results .* \((?<timesum>(([0-1]?\d|2[0-3])(?::([0-5]?\d))?(?::([0-5]?\d))\.\d+))\)$#',
            $values[0]->nodeValue,
            $matches
        );

        $this->assertSame($expectedTimeInSeconds, (string)$matches['timesum']);
    }

    /**
     * @covers ::run
     */
    public function testRunMaxTimeReports(): void
    {
        $expectedTime = '129.25';
        $expectedSuccess= 3;

        $reportPath = TEST_PATH . '/fixtures/reports/html/';
        $task = new HtmlReportMerger();
        $task->setLogger(new Logger(new NullOutput()));

        $resultReport = TEST_PATH . '/result/report_max_time.html';
        $task->maxTime();
        $task
            ->from(
                [
                    $reportPath . 'report_0.html', // this file did not exists and it should not fail
                    $reportPath . 'report_1.html',
                    $reportPath . 'report_2.html',
                    $reportPath . 'report_3.html',
                ]
            )
            ->into($resultReport)
            ->run();

        $this->assertFileExists($resultReport);

        //read first source file as main
        $dstHTML = new DOMDocument();
        $dstHTML->loadHTMLFile($resultReport, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        /** @var DOMNodeList $values */
        $values = (new DOMXPath($dstHTML))
            ->query("//*[contains(@class,'scenarioSuccessValue')]");

        $this->assertCount(1, $values);
        $this->assertSame($expectedSuccess, (int)$values[0]->nodeValue);

        $values = (new DOMXPath($dstHTML))
            ->query("//h1[text() = 'Codeception Results ']");
        preg_match(
            '#^Codeception Results .* \((?<timesum>\d+\.\d+)s\)$#',
            $values[0]->nodeValue,
            $matches
        );
        $executionTime[] = (string)$matches['timesum'];
        usort($executionTime, function ($a, $b) {
            return strcmp($a, $b);
        });
        $this->assertSame($expectedTime, max($executionTime));
    }

    /**
     * @covers ::run
     */
    public function testRunMaxTimeWithCodeception5Reports(): void
    {
        $expectedTime = '02:09.25';
        $expectedSuccess= 3;

        $reportPath = TEST_PATH . '/fixtures/reports/html/';
        $task = new HtmlReportMerger();
        $task->setLogger(new Logger(new NullOutput()));

        $resultReport = TEST_PATH . '/result/report_codeception5_max_time.html';
        $task->maxTime();
        $task
            ->from(
                [
                    $reportPath . 'report_0_codeception5.html', // this file did not exists and it should not fail
                    $reportPath . 'report_1_codeception5.html',
                    $reportPath . 'report_2_codeception5.html',
                    $reportPath . 'report_3_codeception5.html',
                ]
            )
            ->into($resultReport)
            ->run();

        $this->assertFileExists($resultReport);

        //read first source file as main
        $dstHTML = new DOMDocument();
        $dstHTML->loadHTMLFile($resultReport, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        /** @var DOMNodeList $values */
        $values = (new DOMXPath($dstHTML))
            ->query("//*[contains(@class,'scenarioSuccessValue')]");

        $this->assertCount(1, $values);
        $this->assertSame($expectedSuccess, (int)$values[0]->nodeValue);

        $values = (new DOMXPath($dstHTML))
            ->query("//h1[text() = 'Codeception Results ']");
        preg_match(
            '#^Codeception Results .* \((?<timesum>(([0-1]?\d|2[0-3])(?::([0-5]?\d))?(?::([0-5]?\d))\.\d+))\)$#',
            $values[0]->nodeValue,
            $matches
        );
        $executionTime[] = (string)$matches['timesum'];
        usort($executionTime, function ($a, $b) {
            return strcmp($a, $b);
        });
        $this->assertSame($expectedTime, max($executionTime));
    }
}
