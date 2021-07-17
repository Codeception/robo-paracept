<?php
declare(strict_types = 1);

namespace Codeception\Task\Filter;

/**
 * Class DefaultFilter - The Default Filter which is implemented every Time
 */
class DefaultFilter implements Filter
{
    /**
     * @var array
     */
    private $tests;

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
