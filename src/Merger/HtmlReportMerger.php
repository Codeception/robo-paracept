<?php
declare(strict_types = 1);

namespace Codeception\Task\Merger;

use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

/**
 * Generate common HTML report
 * Class MergeHTMLReportsTask
 * @author Kerimov Asif
 */
class HtmlReportMerger extends BaseTask implements ReportMergerTaskInterface
{
    protected $src = [];
    protected $dst;
    protected $countSuccess = 0;
    protected $countFailed = 0;
    protected $countSkipped = 0;
    protected $countIncomplete = 0;
    protected $previousLibXmlUseErrors;

    public function __construct($src = [])
    {
        $this->src = $src;
    }

    public function from($fileName)
    {
        if (is_array($fileName)) {
            $this->src = array_merge($fileName, $this->src);
        } else {
            $this->src[] = $fileName;
        }
        return $this;
    }

    public function into($fileName)
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

        $this->printTaskInfo("Merging HTML reports into {$this->dst}");

        //read first source file as main
        $dstHTML = new \DOMDocument();
        $dstHTML->loadHTMLFile($this->src[0], LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        //main node for all table rows
        $table = (new \DOMXPath($dstHTML))->query("//table")->item(0);

        //prepare reference nodes for envs
        $refnodes = (new \DOMXPath($dstHTML))->query("//div[@class='layout']/table/tr[not(@class)]");

        for ($k=1, $kMax = count($this->src);$k< $kMax;$k++) {
            $srcHTML = new \DOMDocument();
            $src = $this->src[$k];
            $srcHTML->loadHTMLFile($src, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $suiteNodes = (new \DOMXPath($srcHTML))->query("//div[@class='layout']/table/tr");
            $j=0;
            foreach ($suiteNodes as $suiteNode) {
                if ($suiteNode->getAttribute('class') == '') {
                    //move to next reference node
                    $j++;
                    if ($j > $refnodes->length-1) {
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
         * The next 5 functions correct our almost finished final report
         */
        $this->countSummary($dstHTML);
        $this->moveSummaryTable($dstHTML, $table);
        $this->updateSummaryTable($dstHTML);
        $this->updateToolbarTable($dstHTML);
        $this->updateButtons($dstHTML);

        //save final report
        file_put_contents($this->dst, $dstHTML->saveHTML());

        //return to initial statement
        libxml_use_internal_errors($this->previousLibXmlUseErrors);
    }

    /**
     * This function counts all types of tests' scenarios and writes in class members
     * @param $dstFile \DOMDocument - destination file
     */
    private function countSummary($dstFile)
    {
        $tests = (new \DOMXPath($dstFile))->query("//table/tr[contains(@class,'scenarioRow')]");
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
     * @param $dstFile \DOMDocument - destination file
     */
    private function updateSummaryTable($dstFile)
    {
        $dstFile = new \DOMXPath($dstFile);
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
     * @param $dstFile \DOMDocument - destination file
     * @param $node \DOMNode - parent node of Summary table
     */
    private function moveSummaryTable($dstFile, $node)
    {
        $summaryTable = (new \DOMXPath($dstFile))->query("//div[@id='stepContainerSummary']")
            ->item(0)->parentNode->parentNode;
        $node->appendChild($dstFile->importNode($summaryTable, true));
    }

    /**
     * This function updates values in Toolbar block for each type of scenarios (blue block on the left side of the report)
     * @param $dstFile \DOMDocument - destination file
     */
    private function updateToolbarTable($dstFile)
    {
        $dstFile = new \DOMXPath($dstFile);
        $pathFor = function ($type) {
            return "//ul[@id='toolbar-filter']//a[@title='$type']";
        };
        $dstFile->query($pathFor('Successful'))->item(0)->nodeValue = '✔ '.$this->countSuccess;
        $dstFile->query($pathFor('Failed'))->item(0)->nodeValue = '✗ '.$this->countFailed;
        $dstFile->query($pathFor('Skipped'))->item(0)->nodeValue = 'S '.$this->countSkipped;
        $dstFile->query($pathFor('Incomplete'))->item(0)->nodeValue= 'I '.$this->countIncomplete;
    }

    /**
     * This function updates "+" and "-" button for viewing test steps in final report
     * @param $dstFile \DOMDocument - destination file
     */
    private function updateButtons($dstFile)
    {
        $nodes = (new \DOMXPath($dstFile))->query("//div[@class='layout']/table/tr[contains(@class, 'scenarioRow')]");
        for ($i=2;$i<$nodes->length;$i+=2) {
            $n = $i/2 + 1;
            $p = $nodes->item($i)->childNodes->item(1)->childNodes->item(1);
            $table = $nodes->item($i+1)->childNodes->item(1)->childNodes->item(1);
            $p->setAttribute('onclick', "showHide('$n', this)");
            $table->setAttribute('id', "stepContainer".$n);
        }
    }
}
