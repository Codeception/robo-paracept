<?php

declare(strict_types=1);

namespace Codeception\Task\Merger;

use Robo\Task\BaseTask;

abstract class AbstractMerger extends BaseTask implements ReportMergerTaskInterface
{
    /**
     * @var array
     */
    protected $src;

    public function __construct($src = [])
    {
        $this->src = $src;
    }

    /**
     * @inheritDoc
     */
    abstract public function from($fileName);

    /**
     * @inheritDoc
     */
    abstract public function into(string $fileName);

    /**
     * @inheritDoc
     */
    abstract public function run();
}
