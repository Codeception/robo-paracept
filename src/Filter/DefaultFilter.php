<?php

declare(strict_types=1);

namespace Codeception\Task\Filter;

/**
 * Class DefaultFilter - The Default Filter which is implemented by default
 */
class DefaultFilter implements Filter
{
    private array $tests = [];

    /**
     * @inheritDoc
     */
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }

    /**
     * @inheritDoc
     */
    public function filter(): array
    {
        return $this->tests;
    }
}
