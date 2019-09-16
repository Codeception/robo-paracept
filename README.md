robo-paracept
=============

[![Build Status](https://travis-ci.org/Codeception/robo-paracept.svg?branch=master)](https://travis-ci.org/Codeception/robo-paracept)
[![Latest Stable Version](https://poser.pugx.org/codeception/robo-paracept/version)](https://packagist.org/packages/codeception/robo-paracept)
[![Total Downloads](https://poser.pugx.org/codeception/robo-paracept/downloads)](https://packagist.org/packages/codeception/robo-paracept)
[![License](https://poser.pugx.org/codeception/robo-paracept/license)](https://packagist.org/packages/codeception/robo-paracept)

Robo tasks for Codeception tests parallel execution. Requires [Robo Task Runner](http://robo.li)

## Install via Composer

```
"codeception/robo-paracept":"~0.4"
```

Include into your RoboFile

```php
<?php
require_once 'vendor/autoload.php';
require_once 'vendor/codeception/codeception/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Codeception\Task\MergeReports;
    use \Codeception\Task\SplitTestsByGroups;
}
?>
```

### PHPUnit 6 compatiblity

Add ` 'vendor/codeception/codeception/autoload.php'` to enabled PHPUnit 6 class names:

```php
require_once 'vendor/codeception/codeception/autoload.php';
```

## Idea

Parallel execution of Codeception tests can be implemented in different ways.
Depending on a project the actual needs can be different.
Thus, we are going to prepare a set of predefined tasks that can be combined and reconfigured to fit needs.

## Tasks

### SplitTestsByTime

Enable extension for collect execution time of you use taskSplitTestsByTime

```
extensions:
    enabled:
        - Codeception\Task\TimeReporter
```

Loads tests from a folder and distributes them between groups by execution time.

```php
$this->taskSplitTestsByTime(5)
    ->testsFrom('tests/acceptance')
    ->projectRoot('.')
    ->groupsTo('tests/_data/group_')
    ->run();
```

this command need run all tests with `Codeception\Task\TimeReporter` for collect execution time. If you want just split tests between group (and not execute its) you can use SplitTestsByGroups.

### SplitTestsByGroups

```php
$this->taskSplitTestsByGroups(5)
    ->testsFrom('tests/acceptance')
    ->projectRoot('.')
    ->groupsTo('tests/_data/group_')
    ->run();
```

this command uses `Codeception\Test\Loader` to load tests and organize them between group. If you want just split test file and not actual tests (and not load tests into memory) you can use:

```php
$this->taskSplitTestFilesByGroups(5)
   ->testsFrom('tests')
   ->groupsTo('tests/_data/paratest_')
   ->run();
```

### MergeXmlReports

Mergex several XML reports:

```php
$this->taskMergeXmlReports()
    ->from('tests/result/result1.xml')
    ->from('tests/result/result2.xml')
    ->into('tests/result/merged.xml')
    ->run();
```


### MergeHtmlReports

Mergex several HTML reports:

```php
$this->taskMergeHtmlReports()
    ->from('tests/result/result1.html')
    ->from('tests/result/result2.html')
    ->into('tests/result/merged.html')
    ->run();
```
