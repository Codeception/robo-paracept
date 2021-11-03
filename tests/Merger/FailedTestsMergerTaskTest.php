<?php

declare(strict_types=1);

namespace Tests\Codeception\Task\Merger;

use Codeception\Task\Merger\FailedTestsMergerTask;
use Consolidation\Log\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use const Tests\Codeception\Task\TEST_PATH;

final class FailedTestsMergerTaskTest extends TestCase
{
    /**
     * @var int
     */
    private const TEST_FILES_PER_DIR = 5;

    /**
     * @var string
     */
    private const EXPECTED_TEST_MERGED_FILE = TEST_PATH . '/result/failedTests.txt';

    private static array $tmpDirsPattern = [
        'foo' => '/foo_\w+\.txt/',
        'bar' => '/bar_\w+\.txt/',
        'baz' => '/baz_\w+\.txt/',
    ];

    private static array $testContent = [
        'tests/acceptance/%s/baz.php:testA',
        'tests/acceptance/%s/baz.php:testB',
        'tests/acceptance/%s/baz.php:testC',
        'tests/acceptance/%s/baz.php:testD',
        'tests/acceptance/%s/baz.php:testE',
        'tests/acceptance/%s/baz.php:testF',
        'tests/acceptance/%s/baz.php:testG',
        'tests/acceptance/%s/baz.php:testH',
    ];

    private static array $testFiles = [];

    /**
     * Prepare the test files and directories
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $tmpDir = sys_get_temp_dir();
        foreach (array_keys(self::$tmpDirsPattern) as $dir) {
            $tempDir = $tmpDir . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;
            if (!is_dir($tempDir)) {
                mkdir($tempDir);
            }

            $i = 1;
            while ($i <= self::TEST_FILES_PER_DIR) {
                $tempFile = $tempDir . $dir . '_unit' . $i++ . '.txt';
                file_put_contents(
                    $tempFile,
                    implode(
                        PHP_EOL,
                        array_map(
                            static fn(string $filename): string => sprintf($filename, $dir),
                            self::$testContent
                        )
                    )
                );
                self::$testFiles[] = $tempFile;
            }
        }
    }

    /**
     * @covers ::run
     */
    public function testRunSingleFile(): void
    {
        $tmpDir = sys_get_temp_dir() . '/foz/';
        $testFile = $tmpDir . 'foz_123456.txt';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        $this->putContents($testFile);
        $task = new FailedTestsMergerTask();
        $task->setLogger(new Logger(new NullOutput()));
        $task
            ->from($testFile)
            ->into(self::EXPECTED_TEST_MERGED_FILE)
            ->run();

        $this->assertFileExists(self::EXPECTED_TEST_MERGED_FILE);
        $content = explode(PHP_EOL, file_get_contents(self::EXPECTED_TEST_MERGED_FILE));
        $this->assertCount(
            count(self::$testContent),
            $content
        );
    }

    public function testRunWithPathAndFilePatterns(): void
    {
        $task = new FailedTestsMergerTask();
        $task->setLogger(new Logger(new NullOutput()));

        foreach (self::$tmpDirsPattern as $path => $pattern) {
            $task->fromPathWithPattern(
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . $path,
                $pattern
            );
        }

        $task
            ->into(self::EXPECTED_TEST_MERGED_FILE)
            ->run();

        $this->assertFileExists(self::EXPECTED_TEST_MERGED_FILE);
        $this->assertCount(
            (count(self::$testContent) * count(self::$testFiles)),
            explode(PHP_EOL, file_get_contents(self::EXPECTED_TEST_MERGED_FILE))
        );
    }

    public function testRunWithFileAndArrayAndPathWithPatterns(): void
    {
        $tmpDir = sys_get_temp_dir() . '/foz/';
        $testFile = $tmpDir . 'foz_123456.txt';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir);
        }

        $this->putContents($testFile);

        $fileData = [];
        $i = 0;
        while ($i < self::TEST_FILES_PER_DIR) {
            $testFile = $tmpDir . 'foz_' . (123456 + ++$i) . '.txt';
            $this->putContents($testFile);
            $fileData[] = $testFile;
        }

        $task = new FailedTestsMergerTask();
        $task->setLogger(new Logger(new NullOutput()));
        $task->from($testFile);
        $task->from($fileData);
        foreach (self::$tmpDirsPattern as $path => $pattern) {
            $task->fromPathWithPattern(
                sys_get_temp_dir() . DIRECTORY_SEPARATOR . $path,
                $pattern
            );
        }

        $task
            ->into(self::EXPECTED_TEST_MERGED_FILE)
            ->run();

        $this->assertFileExists(self::EXPECTED_TEST_MERGED_FILE);
        $this->assertCount(
            (
                count(self::$testContent) * (
                    count(self::$testFiles)
                    + count($fileData)
                    + count([$testFile])
                )
            ),
            explode(PHP_EOL, file_get_contents(self::EXPECTED_TEST_MERGED_FILE))
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
        unlink(self::EXPECTED_TEST_MERGED_FILE);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass(); // TODO: Change the autogenerated stub
        foreach (self::$testFiles as $file) {
            unlink($file);
        }
    }

    protected function putContents(string $testFile): void
    {
        file_put_contents(
            $testFile,
            implode(
                PHP_EOL,
                array_map(
                    static fn(string $filename): string => sprintf($filename, 'foz'),
                    self::$testContent
                )
            )
        );
    }
}
