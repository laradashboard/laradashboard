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
[[ -n "${PHP_BIN}" ]] || PHP_BIN=php
BACKUP_FILE="${1:-}"

detect_php_binary() {
  local candidate

  if "${PHP_BIN}" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
    return 0
  fi

  for candidate in /opt/alt/php83/usr/bin/php php83 php8.3 /usr/bin/php83; do
    if command -v "${candidate}" >/dev/null 2>&1 \
      && "${candidate}" -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' 2>/dev/null; then
      PHP_BIN="${candidate}"
      return 0
    fi
  done

  echo "PHP 8.3+ is required but not found. Current: $(${PHP_BIN} -r 'echo PHP_VERSION;' 2>/dev/null || echo unknown)"
  echo "On Hostinger: hPanel → Advanced → PHP Configuration → set site to PHP 8.3"
  echo "Or set PHP_BIN to your php83 path before running this script."
  exit 1
}

detect_php_binary
echo "==> Using PHP: ${PHP_BIN} ($(${PHP_BIN} -r 'echo PHP_VERSION;'))"

cd "${APP_PATH}"

if [[ -n "${BACKUP_FILE}" ]]; then
  echo "==> Restoring backup ${BACKUP_FILE}"
  ${PHP_BIN} artisan core:rollback "${BACKUP_FILE}" --force
else
  echo "==> Restoring latest backup"
  ${PHP_BIN} artisan core:rollback --latest --force
fi

echo "==> Rollback complete"
