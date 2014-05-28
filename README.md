robo-paracept
=============

Robo tasks for Codeception tests parallel execution. Requires [Robo Task Runner](https://github.com/Codegyre/Robo)

## Install via Composer

```
"codeception/robo-paracept":"@dev"
```

## Idea

Parallel execution of Codeception tests can be implemented in different ways.
Depending on a project the actual needs can be different.
Thus, we are going to prepare a set of predefined tasks that can be combined and reconfigured to fit needs.

## Tasks

### SplitTestsByGroups

Loads tests from a folder and distributes them between groups.

### MergeReports

Mergex several XML reports
