<?php
declare(strict_types = 1);

namespace Codeception\Task\Splitter;

use Codeception\Test\Descriptor as TestDescriptor;
use Codeception\Util\Annotation;
use InvalidArgumentException;

/**
 * Class TestGroupSplitterTask - Use only Test of a compination of groups and splitted
 * in a given number of groups.
 *
 * Example: use only tests which are in @group 'foo' AND 'bar' but not 'baz'
 *
 * ``` php
 * <?php
 * $this->taskSplitTestGroupIntoGroups(5)
 *    ->testsFrom('tests/unit/Acme')
 *    ->codeceptionRoot('projects/tested')
 *    ->groupIncluded('foo')
 *    ->groupIncluded('bar')
 *    ->groupExcluded('baz')
 *    ->groupsTo('tests/_log/paratest_')
 *    ->run();
 * ?>
 * ```
 */
class TestGroupSplitterTask extends TestsSplitter
{

    /** @var string[] $includedGroups */
    private $includedGroups = [];

    /** @var array[] $excludedGroups */
    private $excludedGroups = [];

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

    public function run()
    {
        $this->claimCodeceptionLoaded();
        $tests = $this->loadTestsByGroup();
        $this->printTaskInfo('Processing ' . count($tests) . ' tests');
    }

    /**
     * Loads the Test which have exactly the given groups included
     * Excludes the tests which matches one of the excluded groups
     * @return array
     */
    protected function loadTestsByGroup(): array
    {
        $testsByGroups = [];
        $tests = $this->loadTests();
        foreach ($tests as $test) {
            [$class, $method] = explode(':', TestDescriptor::getTestSignature($test));
            $annotations = Annotation::forMethod($class, $method)->fetchAll('group');
            if ([] === array_diff($this->getExcludedGroups(), $annotations)) {
                continue;
            }
            if ([] !== array_diff($this->getIncludedGroups(), $annotations)) {
                continue;
            }
            $testsByGroups[] = $test;
        }

        return $testsByGroups;
    }
}
