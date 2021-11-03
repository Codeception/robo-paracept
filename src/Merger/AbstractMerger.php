<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use Robo\Task\BaseTask;

abstract class AbstractMerger extends BaseTask implements ReportMergerTaskInterface
{
    protected array $src = [];

    public function __construct($src = [])
    {
        $this->src = $src;
    }

    /**
     * @inheritDoc
     */
    abstract public function from($fileName): self;

    abstract public function into(string $fileName): self;

    /**
     * @inheritDoc
     */
    abstract public function run();
}
