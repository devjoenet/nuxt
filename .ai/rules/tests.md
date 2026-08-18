---
paths:
  - 'tests/**/*.php'
---

# Tests

## Use Herd for local coverage runs
Run coverage-bearing Pest commands locally through `herd coverage ./vendor/bin/pest` so the coverage driver and configured threshold are active. Plain `php`/Composer coverage invocations may fail because the default local PHP binary does not load a coverage driver.
