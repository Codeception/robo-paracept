<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use InvalidArgumentException;
use Robo\Task\BaseTask;
use RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * Class FailedTestsMergerTask - Task to merge created failed tests report files
 * All paths must be absolute...
 * FailedTestsReporter saved the files into Codeception\Configuration::logDir()
 *
 * ``` php
 * <?php
 * $this->taskMergeFailedTestsReports()
 *    ->from(__DIR__ . 'tests/_data/Acme/failedTests_123.txt')
 *    ->from(__DIR__ . 'tests/_data/failedTests_foo.txt')
 *    ->from([__DIR__ . 'tests/_data/Acme/failedTests_bar.txt', __DIR__ . 'tests/_data/Acme/failedTests_baz.txt',])
 *    ->fromPathWithPattern(__DIR__ . 'tests/_data/failed_1/', '/failedTests_\w+\.txt$/')
 *    ->fromPathWithPattern(__DIR__ . 'tests/_data/failed_2/', '/failedTests_\w+\.txt$/')
 *    ->into(__DIR__ . '/failedTests.txt') // absolute path with Filename
 *    ->run();
 * ?>
 * ```
 */
class FailedTestsMergerTask extends AbstractMerger
{
    public const DEFAULT_PATTERN = '/^failedTests_\w+\.\w+\.txt$/';

    public $pathPatterns = [];

    /**
     * @var string
     */
    private $dest;

    /** @var string[] */
    protected $src = [];

    /**
     * @param string[]|string $fileName
     * @return FailedTestsMergerTask|void
     */
    public function from($fileName): self
    {
        if (!(is_array($fileName) || is_string($fileName) || !empty($fileName))) {
            throw new InvalidArgumentException(
                'The argument must be an array or string and could not be empty.'
            );
        }

        $this->src = array_merge($this->src, (is_string($fileName) ? [$fileName] : $fileName));

        return $this;
    }

    /**
     * Search all report files in path with default pattern or the given pattern
     * @param string $path - The path where the report files exists
     * @param string|null $pattern - The regex pattern for the files (optional)
     * @return $this
     */
    public function fromPathWithPattern(string $path, ?string $pattern = null): self
    {
        $this->pathPatterns[$path] = $pattern ?? self::DEFAULT_PATTERN;

        return $this;
    }

    public function into(string $fileName): self
    {
        $this->dest = $fileName;

        return $this;
    }

    public function run()
    {
        $content = [];
        $files = array_merge($this->src, $this->searchFilesByPattern());
        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }
            $tmpContent = file_get_contents($file);
            if (!$tmpContent) {
                throw new RuntimeException(
                    'Could not read content of reportfile: ' . $file
                );
            }
            $content[] = $tmpContent;
        }

        if (!empty($content)) {
            file_put_contents($this->dest, implode(PHP_EOL, $content));
        }
    }

    /**
     * Search the files by the given path and pattern
     * @return array
     */
    private function searchFilesByPattern(): array
    {
        $results = [];
        foreach ($this->pathPatterns as $path => $pattern) {
            $files = Finder::create()
                ->files()
                ->in($path)
                ->name($pattern ?? self::DEFAULT_PATTERN);
            foreach ($files->getIterator() as $splFileInfo) {
                $results[] = $splFileInfo->getPathname();
            }
        }

        return $results;
    }
}
