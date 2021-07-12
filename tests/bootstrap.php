<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/vendor/codeception/codeception/autoload.php';

use Robo\Robo;

const TEST_PATH = __DIR__;

if (!is_dir($concurrentDirectory = TEST_PATH . '/result/')
    && !mkdir($concurrentDirectory)
    && !is_dir($concurrentDirectory)
) {
    throw new \RuntimeException(
        sprintf('Directory "%s" was not created', $concurrentDirectory)
    );
}
Robo::createContainer();
