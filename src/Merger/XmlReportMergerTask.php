<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

class XmlReportMergerTask extends BaseTask implements ReportMergerTaskInterface
{
    /**
     * @var array|mixed
     */
    protected $src = [];
    /**
     * @var string
     */
    protected $dst;
    /**
     * @var bool
     */
    protected $summarizeTime = true;
    /**
     * @var bool
     */
    protected $mergeRewrite = false;
    /** @var DOMElement[][] */
    protected $suites = [];

    public function __construct(array $src = [])
    {
        $this->src = $src;
    }

    public function sumTime(): void
    {
        $this->summarizeTime = true;
    }

    public function maxTime(): void
    {
        $this->summarizeTime = false;
    }

    public function mergeRewrite(): self
    {
        $this->mergeRewrite = true;

        return $this;
    }

    /**
     * @param array|string $fileName
     * @return $this
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
     * @return $this
     */
    public function into($fileName): self
    {
        $this->dst = $fileName;

        return $this;
    }

    public function run(): void
    {
        if (!$this->dst) {
            throw new TaskException(
                $this,
                "No destination file is set. Use `->into()` method to set result xml"
            );
        }
        $this->printTaskInfo("Merging JUnit XML reports into {$this->dst}");
        $dstXml = new DOMDocument();
        $dstXml->appendChild($dstXml->createElement('testsuites'));

        $this->suites = [];
        foreach ($this->src as $src) {
            $this->printTaskInfo("Processing $src");

            $srcXml = new DOMDocument();
            if (!file_exists($src)) {
                throw new TaskException($this, "XML file $src does not exist");
            }
            $loaded = $srcXml->load($src);
            if (!$loaded) {
                $this->printTaskInfo("<error>File $src can't be loaded as XML</error>");
                continue;
            }
            $suiteNodes = (new DOMXPath($srcXml))->query('//testsuites/testsuite');
            foreach ($suiteNodes as $suiteNode) {
                $suiteNode = $dstXml->importNode($suiteNode, true);
                /** @var $suiteNode DOMElement  * */
                $this->loadSuites($suiteNode);
            }
        }

        $this->mergeSuites($dstXml);

        $dstXml->save($this->dst);
        $this->printTaskInfo(
            "File <info>{$this->dst}</info> saved. " . count($this->suites) . ' suites added'
        );
    }

    protected function loadSuites(DOMElement $current): void
    {
        /** @var DOMNode $node */
        foreach ($current->childNodes as $node) {
            if ($node instanceof DOMElement) {
                if ($this->mergeRewrite) {
                    $this->suites[$current->getAttribute('name')][$node->getAttribute('class') .
                    '::' . $node->getAttribute('name')] = $node->cloneNode(true);
                } else {
                    $this->suites[$current->getAttribute('name')][] = $node->cloneNode(true);
                }
            }
        }
    }

    protected function mergeSuites(DOMDocument $dstXml): void
    {
        foreach ($this->suites as $suiteName => $tests) {
            $resultNode = $dstXml->createElement("testsuite");
            $resultNode->setAttribute('name', $suiteName);
            $data = [
                'tests' => count($tests),
                'assertions' => 0,
                'failures' => 0,
                'errors' => 0,
                'time' => 0,
            ];
            foreach ($tests as $test) {
                $resultNode->appendChild($test);

                $data['assertions'] += (int)$test->getAttribute('assertions');
                $data['time'] = $this->summarizeTime
                    ? ((float)$test->getAttribute('time') + $data['time'])
                    : max($test->getAttribute('time'), $data['time']);

                $data['failures'] += $test->getElementsByTagName('failure')->length;
                $data['errors'] += $test->getElementsByTagName('error')->length;
            }
            foreach ($data as $key => $value) {
                $resultNode->setAttribute($key, (string)$value);
            }
            $dstXml->firstChild->appendChild($resultNode);
        }
    }
}
