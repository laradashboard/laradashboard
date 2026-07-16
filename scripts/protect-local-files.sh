#!/bin/bash
# Deprecated: use scripts/protect-local-files.php (invoked via composer).
# Kept for manual/CI callers that still reference the .sh path.
php "$(dirname "$0")/protect-local-files.php"
