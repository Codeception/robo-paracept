<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use Codeception\Task\Exception\KeyNotFoundException;
use Codeception\Task\Exception\XPathExpressionException;
use DOMAttr;
use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;
use RuntimeException;

/**
 * Generate common HTML report
 * Class MergeHTMLReportsTask
 * @author Kerimov Asif
 */
class HtmlReportMerger extends AbstractMerger
{
    /** @var string[] */
    protected $src = [];
    /** @var string */
    protected $dst;
    /** @var int */
    protected $countSuccess = 0;
    /** @var int */
    protected $countFailed = 0;
    /** @var int */
    protected $countSkipped = 0;
    /** @var int */
    protected $countIncomplete = 0;
    /** @var bool */
    protected $previousLibXmlUseErrors;

    /**
     * @var float
     */
    private $executionTimeSum = 0;

    /**
     * HtmlReportMerger constructor.
     * @param string[] $src - array of source reports
     */
    public function __construct(array $src = [])
    {
        $this->src = $src;
    }

    /**
     * @param string[]|string $fileName - a single report file or array of report files
     * @return $this|HtmlReportMerger
     */
    public function from($fileName): self
    {
        if (is_array($fileName)) {
            $this->src = array_merge($fileName, $this->src);
        } else {
            $this->src[] = $fileName;
        }
        return $this;
    }

    /**
     * @param string $fileName
     * @return $this|HtmlReportMerger
     */
    public function into(string $fileName): self
    {
        $this->dst = $fileName;
        return $this;
    }

    public function run()
    {
        //save initial statament and switch on use_internal_errors mode
        $this->previousLibXmlUseErrors = libxml_use_internal_errors(true);

        if (!$this->dst) {
            libxml_use_internal_errors($this->previousLibXmlUseErrors);
            throw new TaskException($this, "No destination file is set. Use `->into()` method to set result HTML");
        }

        $this->printTaskInfo("Remove not existing HTML reports...");
        foreach ($this->src as $index => $item) {
            if (!file_exists($item)) {
                unset($this->src[$index]);
                $this->printTaskWarning(
                    "HTML report {$item} did not exist and was removed from merge list"
                );
            }
        }
        // Resetting keys
        $this->src = array_values($this->src);

        $this->printTaskInfo("Merging HTML reports into {$this->dst}");

        //read first source file as main
        $dstHTML = new DOMDocument();
        $dstHTML->loadHTMLFile($this->src[0], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $this->countExecutionTime($dstHTML);
        //main node for all table rows
        $nodeList = (new DOMXPath($dstHTML))->query("//table");
        if (!$nodeList) {
            throw XPathExpressionException::malformedXPath("//table");
        }
        $index = 0;
        /** @var DOMNode $table */
        $table = $nodeList->item($index);
        if (null === $table) {
            throw new KeyNotFoundException('Could not find table item at pos: ' . $index);
        }
        //prepare reference nodes for envs
        $xpathExprRefNodes = "//div[@class='layout']/table/tr[not(@class)]";
        $refnodes = (new DOMXPath($dstHTML))->query($xpathExprRefNodes);
        if (!$refnodes) {
            throw XPathExpressionException::malformedXPath($xpathExprRefNodes);
        }
        for ($k = 1, $kMax = count($this->src); $k < $kMax; $k++) {
            $src = $this->src[$k];
            if (!file_exists($src) || !is_readable($src)) {
                $this->printTaskWarning('File did not exists or is not readable: ' . $src);
                continue;
            }
            $srcHTML = new DOMDocument();
            $srcHTML->loadHTMLFile($src, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $this->countExecutionTime($srcHTML);
            $xpathExprSuiteNodes = "//div[@class='layout']/table/tr";
            $suiteNodes = (new DOMXPath($srcHTML))->query($xpathExprSuiteNodes);
            if (!$suiteNodes) {
                throw XPathExpressionException::malformedXPath($xpathExprSuiteNodes);
            }
            $j = 0;
            foreach ($suiteNodes as $suiteNode) {
                if ($suiteNode->getAttribute('class') == '') {
                    //move to next reference node
                    $j++;
                    if ($j > $refnodes->length - 1) {
                        break;
                    }
                    continue;
                }
                //insert nodes before current reference node
                $suiteNode = $dstHTML->importNode($suiteNode, true);
                $table->insertBefore($suiteNode, $refnodes->item($j));
            }
        }

        /**
         * The next 6 functions correct our almost finished final report
         */
        $this->countSummary($dstHTML);
        $this->moveSummaryTable($dstHTML, $table);
        $this->updateSummaryTable($dstHTML);
        $this->updateToolbarTable($dstHTML);
        $this->updateButtons($dstHTML);
        $this->updateHeaderLine($dstHTML);

        //save final report
        file_put_contents($this->dst, $dstHTML->saveHTML());

        //return to initial statement
        libxml_use_internal_errors($this->previousLibXmlUseErrors);
    }

    /**
     * This function sums all execution time of each report
     * @param DOMDocument $dstFile
     * @throws XPathExpressionException
     */
    private function countExecutionTime(DOMDocument $dstFile): void
    {
        $xpathHeadline = "//h1[text() = 'Codeception Results ']";
        $nodeList = (new DOMXPath($dstFile))
            ->query($xpathHeadline);
        if (!$nodeList) {
            throw XPathExpressionException::malformedXPath($xpathHeadline);
        }
        $pregResult = preg_match(
            '/^Codeception Results .* \((?<timesum>\d+\.\d+)s\)$/',
            $nodeList[0]->nodeValue,
            $matches
        );

        if (false === $pregResult) {
            throw new RuntimeException('Regexpression is malformed');
        }

        if (0 === $pregResult) {
            return;
        }

        $this->executionTimeSum += (float)$matches['timesum'];
    }

    /**
     * @param DOMDocument $dstFile
     * @throws XPathExpressionException
     */
    private function updateHeaderLine(DOMDocument $dstFile): void
    {
        $xpathHeadline = "//h1[text() = 'Codeception Results ']";
        $nodeList = (new DOMXPath($dstFile))
            ->query($xpathHeadline);
        if (!$nodeList) {
            throw XPathExpressionException::malformedXPath($xpathHeadline);
        }
        /** @var DOMNode $executionTimeNode */
        $executionTimeNode = $nodeList[0]->childNodes[1]->childNodes[1];
        /** @var DOMAttr $statusAttr */
        $statusNode = $nodeList[0]->childNodes[1]->childNodes[0];
        $statusAttr = $statusNode->attributes[0];
        if (0 !== $this->countFailed) {
            $statusNode->nodeValue = 'FAILED';
            $statusAttr->value = 'color: red';
        }
        $executionTimeNode->nodeValue = " ({$this->executionTimeSum}s)";
    }

    /**
    * This function counts all types of tests' scenarios and writes in class members
    * @param DOMDocument $dstFile - destination file
    * @throws XPathExpressionException
    */
    private function countSummary(DOMDocument $dstFile): void
    {
        $xpathExprTests = "//table/tr[contains(@class,'scenarioRow')]";
        /** @var DOMNodeList $tests */
        $tests = (new DOMXPath($dstFile))->query($xpathExprTests);
        if (!$tests) {
            throw XPathExpressionException::malformedXPath($xpathExprTests);
        }
        foreach ($tests as $test) {
            $class = str_replace('scenarioRow ', '', $test->getAttribute('class'));
            switch ($class) {
                case 'scenarioSuccess':
                    $this->countSuccess += 0.5;
                    break;
                case 'scenarioFailed':
                    $this->countFailed += 0.5;
                    break;
                case 'scenarioSkipped':
                    $this->countSkipped += 0.5;
                    break;
                case 'scenarioIncomplete':
                    $this->countIncomplete += 0.5;
                    break;
            }
        }
    }

    /**
     * This function updates values in Summary block for each type of scenarios
     * @param DOMDocument $dstFile - destination file
     */
    private function updateSummaryTable(DOMDocument $dstFile)
    {
        $dstFile = new DOMXPath($dstFile);
        $pathFor = function ($type) {
            return "//div[@id='stepContainerSummary']//td[@class='$type']";
        };
        $dstFile->query($pathFor('scenarioSuccessValue'))->item(0)->nodeValue = $this->countSuccess;
        $dstFile->query($pathFor('scenarioFailedValue'))->item(0)->nodeValue = $this->countFailed;
        $dstFile->query($pathFor('scenarioSkippedValue'))->item(0)->nodeValue = $this->countSkipped;
        $dstFile->query($pathFor('scenarioIncompleteValue'))->item(0)->nodeValue = $this->countIncomplete;
    }

    /**
     * This function moves Summary block in the bottom of result report
     * @param $dstFile DOMDocument - destination file
     * @param $node DOMNode - parent node of Summary table
     */
    private function moveSummaryTable(DOMDocument $dstFile, DOMNode $node)
    {
        $summaryTable = (new DOMXPath($dstFile))->query("//div[@id='stepContainerSummary']")
            ->item(0)->parentNode->parentNode;
        $node->appendChild($dstFile->importNode($summaryTable, true));
    }

    /**
     * This function updates values in Toolbar block for each type of scenarios
     * (blue block on the left side of the report)
     * @param DOMDocument $dstFile  - destination file
     */
    private function updateToolbarTable(DOMDocument $dstFile)
    {
        $dstFile = new DOMXPath($dstFile);
        $pathFor = static function (string $type): string {
            return "//ul[@id='toolbar-filter']//a[@title='$type']";
        };
        $dstFile->query($pathFor('Successful'))->item(0)->nodeValue = '✔ ' . $this->countSuccess;
        $dstFile->query($pathFor('Failed'))->item(0)->nodeValue = '✗ ' . $this->countFailed;
        $dstFile->query($pathFor('Skipped'))->item(0)->nodeValue = 'S ' . $this->countSkipped;
        $dstFile->query($pathFor('Incomplete'))->item(0)->nodeValue = 'I ' . $this->countIncomplete;
    }

    /**
     * This function updates "+" and "-" button for viewing test steps in final report
     * @param $dstFile DOMDocument - destination file
     * @throws XPathExpressionException
     */
    private function updateButtons(DOMDocument $dstFile)
    {
        $xpathExprNodes = "//div[@class='layout']/table/tr[contains(@class, 'scenarioRow')]";
        $nodes = (new DOMXPath($dstFile))->query($xpathExprNodes);
        if (!$nodes) {
            throw XPathExpressionException::malformedXPath($xpathExprNodes);
        }
        for ($i = 2; $i < $nodes->length; $i += 2) {
            $n = $i / 2 + 1;
            $p = $nodes->item($i)->childNodes->item(1)->childNodes->item(1);
            $table = $nodes->item($i + 1)->childNodes->item(1)->childNodes->item(1);
            $p->setAttribute('onclick', "showHide('$n', this)");
            $table->setAttribute('id', "stepContainer" . $n);
        }
    }
}
