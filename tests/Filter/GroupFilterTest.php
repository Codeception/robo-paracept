<?php

declare(strict_types=1);

namespace Tests\Codeception\Task\Filter;

use Codeception\Task\Filter\GroupFilter;
use Codeception\Test\Loader as TestLoader;
use InvalidArgumentException;
use PHPUnit\Framework\SelfDescribing;
use PHPUnit\Framework\TestCase;
use const Tests\Codeception\Task\TEST_PATH;

/**
 * Class GroupFilterTest
 *
 * @coversDefaultClass \Codeception\Task\Filter\GroupFilter
 */
final class GroupFilterTest extends TestCase
{
    /**
     * @testdox Test that the excluded group is unique in the array
     * @covers ::getExcludedGroups
     * @covers ::groupExcluded
     */
    public function testGetExcludedGroups(): void
    {
        $groupsToAdd = [
            'foo',
            'bar',
            'baz',
            'foo',
            'Foo',
            'baZ',
        ];

        $expected = [
            'foo',
            'bar',
            'baz',
            'Foo',
            'baZ',
        ];

        $groupTo = TEST_PATH . '/result/group_';
        $task = new GroupFilter();
        foreach ($groupsToAdd as $group) {
            $task->groupExcluded($group);
        }

        $this->assertSame($expected, $task->getExcludedGroups());
    }

    /**
     * @covers ::getIncludedGroups
     * @covers ::groupIncluded
     */
    public function testGetIncludedGroups(): void
    {
        $groupsToAdd = [
            'foo',
            'bar',
            'baz',
            'foo',
            'Foo',
            'baZ',
        ];

        $expected = [
            'foo',
            'bar',
            'baz',
            'Foo',
            'baZ',
        ];

        $task = new GroupFilter();
        foreach ($groupsToAdd as $group) {
            $task->groupIncluded($group);
        }

        $this->assertSame($expected, $task->getIncludedGroups());
    }

    /**
     * @covers ::groupIncluded
     * @covers ::groupExcluded
     */
    public function testDoNotAddGroupToIncludedAndExcluded(): void
    {
        $task = new GroupFilter();
        $task->groupIncluded('foo');
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessageMatches(
            '/^You can mark group "\w+" only as included OR excluded.$/'
        );
        $task->groupExcluded('foo');
    }

    /**
     * @covers ::groupIncluded
     * @covers ::groupExcluded
     */
    public function testDoNotAddGroupToExcludedAndIncluded(): void
    {
        $task = (new GroupFilter())->groupExcluded('bar');
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessageMatches(
            '/^You can mark group "\w+" only as included OR excluded.$/'
        );
        $task->groupIncluded('bar');
    }

    /**
     * @covers ::filter
     */
    public function testFilterWithCestFiles(): void
    {
        $loader = new TestLoader(['path' => TEST_PATH . '/fixtures/Cests']);
        $loader->loadTests(TEST_PATH . '/fixtures/Cests');

        $tests = $loader->getTests();
        $this->assertCount(3, $tests);
        // Filter with no groups should return all tests
        $filter = new GroupFilter();
        $filter->setTests($tests);

        $filtered = $filter->filter();
        $this->assertCount(3, $filtered);
        // Filter with group foo should return 2 Tests
        $filter->groupIncluded('foo');
        $filtered = $filter->filter();
        $this->assertCount(2, $filtered);
        // Filter with group foo from before and new excluded group no should return 1 test
        $filter->groupExcluded('no');
        $filtered = $filter->filter();
        $this->assertCount(1, $filtered);
        $filter->reset();
        $filtered = $filter->filter();
        $this->assertCount(3, $filtered);
        foreach ($filtered as $test) {
            $this->assertInstanceOf(SelfDescribing::class, $test);
        }
    }

    public function testFilterWithUnitTests(): void
    {
        $loader = new TestLoader(['path' => TEST_PATH . '/fixtures/Unit']);
        $loader->loadTests(TEST_PATH . '/fixtures/Unit');

        $tests = $loader->getTests();
        $this->assertCount(4, $tests);
        // Filter with no groups should return all tests
        $filter = new GroupFilter();
        $filter->setTests($tests);

        $filtered = $filter->filter();
        $this->assertCount(4, $filtered);
        // Filter with group foo should return 2 Tests
        $filter->groupIncluded('foo');
        $filtered = $filter->filter();
        $this->assertCount(3, $filtered);
        // Filter with group foo from before and new excluded group no should return 1 test
        $filter->groupExcluded('no');
        $filtered = $filter->filter();
        $this->assertCount(1, $filtered);
        $filter->reset();
        $filtered = $filter->filter();
        $this->assertCount(4, $filtered);
        foreach ($filtered as $test) {
            $this->assertInstanceOf(SelfDescribing::class, $test);
        }
    }
}
