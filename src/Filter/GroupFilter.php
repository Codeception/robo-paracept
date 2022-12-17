<?php

declare(strict_types=1);

namespace Codeception\Task\Filter;

use Codeception\Lib\GroupManager;
use Codeception\Test\Descriptor as TestDescriptor;
use Codeception\Util\Annotation;
use InvalidArgumentException;
use PHPUnit\Framework\SelfDescribing;

/**
 * Class GroupFilter - allows to filter tests by the @group Annotation
 *
 * @see \Tests\Codeception\Task\Filter\GroupFilterTest
 */
class GroupFilter implements Filter
{
    /** @var string[] $includedGroups */
    private array $includedGroups = [];

    private array $excludedGroups = [];

    /** @var SelfDescribing[] */
    private array $tests = [];

    public function reset(): void
    {
        $this->resetIncludedGroups();
        $this->resetExcludedGroups();
    }

    public function resetIncludedGroups(): void
    {
        $this->includedGroups = [];
    }

    public function resetExcludedGroups(): void
    {
        $this->excludedGroups = [];
    }

    /**
     * @return SelfDescribing[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * Adds a group name to the excluded array
     */
    public function groupExcluded(string $group): self
    {
        if (in_array($group, $this->getIncludedGroups(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'You can mark group "%s" only as included OR excluded.',
                    $group
                )
            );
        }

        if (!in_array($group, $this->getExcludedGroups(), true)) {
            $this->excludedGroups[] = $group;
        }

        return $this;
    }

    /**
     * Adds a group name to the included array
     */
    public function groupIncluded(string $group): self
    {
        if (in_array($group, $this->getExcludedGroups(), true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'You can mark group "%s" only as included OR excluded.',
                    $group
                )
            );
        }

        if (!in_array($group, $this->getIncludedGroups(), true)) {
            $this->includedGroups[] = $group;
        }

        return $this;
    }

    public function getExcludedGroups(): array
    {
        return $this->excludedGroups;
    }

    /**
     * @return string[]
     */
    public function getIncludedGroups(): array
    {
        return $this->includedGroups;
    }

    /**
     * @param SelfDescribing[] $tests
     */
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }

    /**
     * Filter the tests by the given included and excluded @group annotations
     *
     * @return \PHPUnit\Framework\SelfDescribing[]
     */
    public function filter(): array
    {
        $groupManager = new GroupManager([]);

        $testsByGroups = [];
        foreach ($this->getTests() as $test) {
            if (!($test instanceof SelfDescribing)) {
                throw new InvalidArgumentException(
                    'Tests must be an instance of ' . SelfDescribing::class
                );
            }

            $groups = $groupManager->groupsForTest($test);

            if (!$groups && $test instanceof \PHPUnit\Framework\DataProviderTestSuite) {
                /** @var \PHPUnit\Framework\DataProviderTestSuite $dataProviderTestSuite */
                $dataProviderTestSuite = $test;
                // By definition (a) all tests of dataprovider test suite are the same test
                // case definition, and (b) there is at least one test case
                $firstDataProviderTest = $dataProviderTestSuite->tests()[0];
                $groups = $groupManager->groupsForTest($firstDataProviderTest);
            }

            if (
                !empty($this->getExcludedGroups())
                && [] === array_diff($this->getExcludedGroups(), $groups)
            ) {
                continue;
            }

            if (
                !empty($this->getIncludedGroups())
                && [] !== array_diff($this->getIncludedGroups(), $groups)
            ) {
                continue;
            }

            $testsByGroups[] = $test;
        }

        return $testsByGroups;
    }
}
