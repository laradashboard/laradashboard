#!/usr/bin/env bash
#
# Restore the latest core upgrade backup.
#
# Usage:
#   ./deploy/rollback.sh [backup-filename]
#
# Required environment variables:
#   APP_PATH — absolute path to the Laravel application
#
set -euo pipefail

APP_PATH="${APP_PATH:?APP_PATH is required}"
PHP_BIN="${PHP_BIN:-php}"
BACKUP_FILE="${1:-}"

cd "${APP_PATH}"

if [[ -n "${BACKUP_FILE}" ]]; then
  echo "==> Restoring backup ${BACKUP_FILE}"
  ${PHP_BIN} artisan core:rollback "${BACKUP_FILE}" --force
else
  echo "==> Restoring latest backup"
  ${PHP_BIN} artisan core:rollback --latest --force
fi

echo "==> Rollback complete"
