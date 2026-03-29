---
description: "Run tests for a specific module or all module-related tests"
allowed-tools: ["Bash", "Read", "Glob", "Grep"]
---

# Run Module Tests

Run tests for a specific LaraDashboard module.

## Instructions

1. Parse arguments: $ARGUMENTS
   - If a module name is given, run tests for that module
   - If empty, run all module-related tests

2. Determine test location:
   - Module tests: `modules/{module}/tests/`
   - Core module command tests: `tests/Feature/Commands/Module*`
   - Core module tests: `tests/Feature/Modules/`

3. Run tests:
   ```bash
   # For a specific module's tests
   php artisan test modules/{module}/tests/

   # For core module tests
   php artisan test --filter=Module

   # For a specific test class
   php artisan test --filter={TestClassName}
   ```

4. If tests fail:
   - Read the failing test file
   - Read the source code being tested
   - Identify the issue and suggest a fix
   - Do NOT auto-fix unless the user asks

5. Report results with pass/fail counts and any failures
