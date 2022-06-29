# Contributing Guidelines
So you want to contribute? Awesome! 

# Contributing through PRs
Please make sure you verify some things
before submitting a PR. 

> All PRs should be opened targeting the **main** branch

## Local checks
When adding code please verify this command runs without issue. It will run Linting, Codestyle, Static Analysis, Mess Detection, and Unit tests.

> greater than 90% coverage on unit tests is expected

```bash
composer run check
```

If there are any codestyle issuse from the sniff step you can run
```bash
composer run fix
```
then commit any of the fixes it makes.


# Submitting Issues
If you have any suggestions, features, bug fixes, etc. please feel free to submit an issue.

* Look for any issues with the same details first
* please include as much information as possible
* if a bug please include reproduceable

