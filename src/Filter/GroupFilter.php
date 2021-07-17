<?php
declare(strict_types = 1);

namespace Codeception\Task\Filter;

use Codeception\Test\Descriptor as TestDescriptor;
use Codeception\Util\Annotation;
use InvalidArgumentException;
use PHPUnit\Framework\SelfDescribing;

/**
 * Class GroupFilter - allows to filter tests by the @group Annotation
 */
class GroupFilter implements Filter
{

    /** @var string[] $includedGroups */
    private $includedGroups = [];

    /** @var array[] $excludedGroups */
    private $excludedGroups = [];
    /**
     * @var SelfDescribing[]
     */
    private $tests = [];

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
     * @param string $group
     * @return $this
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
     * @param string $group
     * @return $this
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

    /**
     * @return array[]
     */
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
     */
    public function filter(): array
    {
        $testsByGroups = [];
        foreach ($this->getTests() as $test) {
            [$class, $method] = explode(':', TestDescriptor::getTestSignature($test));
            $annotations = Annotation::forMethod($class, $method)->fetchAll('group');
            if (!empty($this->getExcludedGroups())
                && [] === array_diff($this->getExcludedGroups(), $annotations)
            ) {
                continue;
            }
            if (!empty($this->getIncludedGroups())
                && [] !== array_diff($this->getIncludedGroups(), $annotations)
            ) {
                continue;
            }
            $testsByGroups[] = $test;
        }

        return $testsByGroups;
    }
}
