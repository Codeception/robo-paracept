robo-paracept
=============

[![PHP Composer](https://github.com/Codeception/robo-paracept/actions/workflows/php.yml/badge.svg)](https://github.com/Codeception/robo-paracept/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/codeception/robo-paracept/version)](https://packagist.org/packages/codeception/robo-paracept)
[![Total Downloads](https://poser.pugx.org/codeception/robo-paracept/downloads)](https://packagist.org/packages/codeception/robo-paracept)
[![License](https://poser.pugx.org/codeception/robo-paracept/license)](https://packagist.org/packages/codeception/robo-paracept)

Robo tasks for Codeception tests parallel execution. Requires [Robo Task Runner](http://robo.li)

## Install via Composer

```
composer require codeception/robo-paracept --dev
```

Include into your RoboFile

```php
<?php
require_once 'vendor/autoload.php';
require_once 'vendor/codeception/codeception/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use Codeception\Task\Merger\ReportMerger;
    use Codeception\Task\Splitter\TestsSplitterTrait;
}
?>
```

## Idea

Parallel execution of Codeception tests can be implemented in different ways.
Depending on a project the actual needs can be different.
So we prepared a set of predefined Robo tasks that can be combined and reconfigured to fit your needs.

## Tasks

### SplitTestsByGroups

Load tests from a folder and distributes them between groups.

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
### SplitTestsByTime

Enable extension for collect execution time of you use taskSplitTestsByTime

```
extensions:
    enabled:
        - Codeception\Task\Extension\TimeReporter
```

Load tests from a folder and distributes them between groups by execution time.

```php
$this->taskSplitTestsByTime(5)
    ->testsFrom('tests/acceptance')
    ->projectRoot('.')
    ->groupsTo('tests/_data/group_')
    ->run();
```

this command need run all tests with `Codeception\Task\TimeReporter` for collect execution time. If you want just split tests between group (and not execute its) you can use SplitTestsByGroups. **Please be aware**: This task will not consider any 'depends' annotation!

### SplitFailedTests

Enable extension for collect failed tests if you use taskSplitFailedTests

```
extensions:
    enabled:
        - Codeception\Task\Extension\FailedTestsReporter
```

Load the failed Tests from a reportfile into the groups:
- Default report path is: `Configuration::outputDir() . 'failedTests.txt'`
```php
$this
    ->taskSplitFailedTests(5)
    ->setReportPath('tests/_output/' . FailedTestsReporter::REPORT_NAME)
    ->groupsTo('tests/_data/group_')
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


## Filters

You can use a custom filter to select the necessary tests.

Two filters already included: DefaultFilter, GroupFilter

* **DefaultFilter** is enabled by default, takes all tests.
* **GroupFilter** _(Can only be used by taskSplitTestsByGroups)_, allows you to filter the loaded tests by the given groups. You have the possibility to declare groups which you want to include or exclude. If you declare foo and bar as included, only tests with this both group annotations will be matched. The same thing is happend when you add excluded groups. If you combine the included and excluded group the only tests which have exactly the correct group annotations for the included items and none of the excluded items.

You can add as many filters as you want. The FIFO (First In - First Out) principle applies. The next filter will only get the result of the filter before.

### Usage

For example, you want all tests which have in the doc comment the groups 'foo' AND 'bar' but not 'baz' then you can do it like this:

```php 
$filter = new GroupFilter();
$filter
    ->groupIncluded('foo')
    ->groupIncluded('bar')
    ->groupExcluded('baz');

$this->taskSplitTestsByGroups(5)
   ->testsFrom('tests')
   ->groupsTo('tests/_data/paratest_')
   ->addFilter($filter)
   ->run();
```

Now create your own filter class:
```php 
<?php

declare(strict_types=1);

namespace ...;

use Codeception\Task\Filter\DefaultFilter;

class CustomFilter extends DefaultFilter {

}
```

The TestFileSplitterTask.php pushes an array of SplFileInfo Objects to the filter.  
The TestsSplitterTask.php pushes an array of SelfDescribing Objects to the filter.

### License MIT
