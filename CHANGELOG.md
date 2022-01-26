## [2.0.0](https://github.com/Codeception/robo-paracept/releases/tag/2.0.0) Major Release

robo-paracept 2.0 is now released

- support for PHP 7.4 , 8.0, 8.1
- refactorings

Thanks to [@DavertMik](https://github.com/DavertMik) and [@TavoNiievez](https://github.com/TavoNiievez) for their contributions.

## [1.3.1](https://github.com/Codeception/robo-paracept/releases/tag/1.3.1) Preloading Codeception configuration

* Loading default Codeception config to detect the main directory path
* Show warning if Codeception config was not loaded

## [1.3.0](https://github.com/Codeception/robo-paracept/releases/tag/1.3.0) Split group improvements

* Load PHPUnit bridge of Codeception before splitting tests
* All `Split*` to return result objects (readme updated)
* Warn if root directory for Codeception is not set when splitting by groups
* Improved matching groups by using GroupManager

See [#96](https://github.com/Codeception/robo-paracept/pull/96)

## [1.2.4](https://github.com/Codeception/robo-paracept/releases/tag/1.2.4) Bugfix Release HTMLMerger

If one of the html reports wasn't created by a parallel job the merger will not longer throw an exception.
The Merger will show a warning that an expected html report wasn't found.

## What's Changed
* Update CHANGELOG.md by [@Arhell](https://github.com/Arhell) in https://github.com/Codeception/robo-paracept/pull/90
* Bugfix/91 fixing html merger by [@vansari](https://github.com/vansari) in https://github.com/Codeception/robo-paracept/pull/92

**Full Changelog**: https://github.com/Codeception/robo-paracept/compare/1.2.3...1.2.4

## [1.2.3](https://github.com/Codeception/robo-paracept/releases/tag/1.2.3) Bugfix Release XmlMerger

Fix the issue that an exception is thrown if a xml report does not exists.

## [1.2.2](https://github.com/Codeception/robo-paracept/releases/tag/1.2.2) Bugfix HTML Merger

Fix [#31](https://github.com/Codeception/robo-paracept/issues/31)

## [1.2.1](https://github.com/Codeception/robo-paracept/releases/tag/1.2.1) Bugfix

Pull Request: [#82](https://github.com/Codeception/robo-paracept/pull/82)

Calculation number of tests in groups in class TestsSplitterTask did not work as expected.
If you have a high number of Tests it was possible that the last group received a huge number of tests while all others had a stable small number of tests.

- Fixing calculation number of tests in Groups
- Using PHP Built In round() instead of floor()

## [1.2.0](https://github.com/Codeception/robo-paracept/releases/tag/1.2.0)

- Bugfix for extension FailedTestsReporter and  new FailedTestsMergerTask [#75](https://github.com/Codeception/robo-paracept/pull/75)
    - generated Files will not be overwriten anymore
    - each generated File has now a uniqid-suffix (PHP Function uniqid('', true))
    - merge generated report files from FailedTestsReporter into single file
    - posibility to merge also another files into a single file
    
- Bugfix src/Splitter/TestsSplitter.php::testsFrom [#78](https://github.com/Codeception/robo-paracept/pull/78) [@ccsuperstar](https://github.com/ccsuperstar)
    - revert string type hint and allow array or string again

## [1.1.1](https://github.com/Codeception/robo-paracept/releases/tag/1.1.1) Bugfix

* Fixed return type declaraton [#68](https://github.com/Codeception/robo-paracept/pull/68)

## [1.1.0](https://github.com/Codeception/robo-paracept/releases/tag/1.1.0) Robo-Paracept 1.1

* SplitFailedTests task added to split by groups failed tests only [#65](https://github.com/Codeception/robo-paracept/pull/65)
* Fixed return type in taskSplitTestFilesByGroups [#62](https://github.com/Codeception/robo-paracept/pull/62)

## [1.0.0](https://github.com/Codeception/robo-paracept/releases/tag/1.0.0) Robo-Paracept 1.0

Big day for Robo-Paracept. The first stable version is released! ✈️

### Changes

* **Support for modern PHP 7.3, 7.4, 8.0**
* Added support for the latest [Robo task runner](https://robo.li)
* Added **Filters** to select tests before splitting them
* Added **SplitByTime** task to use time statistics of previous runs to balance groups of tests. Thanks to [@ivan1986](https://github.com/ivan1986)

## [0.4.2](https://github.com/Codeception/robo-paracept/releases/tag/0.4.2) Resolve dependencies when splitting tests

[#46](https://github.com/Codeception/robo-paracept/pull/46)

## [0.4.1](https://github.com/Codeception/robo-paracept/releases/tag/0.4.1) Release with new PHPUnit support

* PHPUnit 6.x support in split  [#45](https://github.com/Codeception/robo-paracept/pull/45)
* follow symlinks while scanning for tests [#44](https://github.com/Codeception/robo-paracept/pull/44)

## [0.4.0](https://github.com/Codeception/robo-paracept/releases/tag/0.4.0) Minor improvements

* [#37](https://github.com/Codeception/robo-paracept/pull/37) Added `excluePath` option to `SplitTestsByGroups` task. By [@thejanasatan](https://github.com/thejanasatan)
* [#36](https://github.com/Codeception/robo-paracept/pull/36) Added mergeRewrite to merge reports by [@maxgorovenko](https://github.com/maxgorovenko)
* [#30](https://github.com/Codeception/robo-paracept/pull/30) Fixed execute test name from data provider by [@ivan1986](https://github.com/ivan1986)

Also PHPUnit 6 compatibility can be achieved by including Codeception's autoloader:

```php
require  'vendor/codeception/codeception/autoload.php'
```

See [#35 (comment)](https://github.com/Codeception/robo-paracept/issues/35#issuecomment-311605115)

## [0.3.1](https://github.com/Codeception/robo-paracept/releases/tag/0.3.1) 0.3.1: Merge pull request #27 from dhiva/master

Improved HTML report merge [#27](https://github.com/Codeception/robo-paracept/pull/27)

## [0.3.0](https://github.com/Codeception/robo-paracept/releases/tag/0.3.0) Robo 1.0 compatibility

* Robo 1.0 compatibility (Merged [#19](https://github.com/Codeception/robo-paracept/issues/19) , Fixed [#16](https://github.com/Codeception/robo-paracept/issues/16) [#17](https://github.com/Codeception/robo-paracept/pull/17))
* Support for `.feature` files in `SplitGroups`. Merged [#23](https://github.com/Codeception/robo-paracept/pull/23)

## [0.2.0](https://github.com/Codeception/robo-paracept/releases/tag/0.2.0) Support for Robo 0.7-1.0

Fixed using with Robo >= 0.7

* [#12](https://github.com/Codeception/robo-paracept/pull/12)
* [#15](https://github.com/Codeception/robo-paracept/pull/15)
* Fixed [#14](https://github.com/Codeception/robo-paracept/issues/14)

## [0.1.1](https://github.com/Codeception/robo-paracept/releases/tag/0.1.1) Codeception v2.2 and Robo 0.7 compat

Reference

https://codeception.com/docs/12-ParallelExecution#Robo

## [0.1.0](https://github.com/Codeception/robo-paracept/releases/tag/0.1.0)

To be compatible with codeception 2.2.2
