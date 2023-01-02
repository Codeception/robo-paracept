<?php

declare(strict_types=1);

namespace Codeception\Task\Filter;

/**
 * Interface Filter allows to Filter the Files before split to groups
 * Every new filter must implements this Interface to ensure compatibility
 */
interface Filter
{
    /**
     * Set the collection of tests which should be filtered
     */
    public function setTests(array $tests): void;

    /**
     * Returns the filtered Tests
     */
    public function filter(): array;
}
