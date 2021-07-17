<?php
declare(strict_types = 1);

namespace Codeception\Task\Filter;

use PHPUnit\Framework\SelfDescribing;

/**
 * Interface Filter allows to Filter the Files before split to groups
 * Every new filter must implements this Interface to ensure compatibility
 */
interface Filter
{
    /**
     * Set the collection of tests which should be filtered
     * @param SelfDescribing[] $tests
     */
    public function setTests(array $tests): void;

    /**
     * Returns the filtered Tests
     * @return SelfDescribing[]
     */
    public function filter(): array;
}