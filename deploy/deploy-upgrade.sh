#!/usr/bin/env bash
#
# Run a full core upgrade on a remote installation via SSH or locally.
#
# Usage:
#   ./deploy/deploy-upgrade.sh 1.3.0
#
# Required environment variables:
#   APP_PATH — absolute path to the Laravel application
#
# Optional:
#   PHP_BIN          — php binary (default: php)
#   SKIP_BACKUP      — set to 1 to pass --no-backup to core:upgrade
#   ROLLBACK_ON_FAIL — set to 1 to restore latest backup if verify fails (default: 1)
#
set -euo pipefail

VERSION="${1:?Usage: deploy-upgrade.sh <version>}"
APP_PATH="${APP_PATH:?APP_PATH is required}"
PHP_BIN="${PHP_BIN:-php}"
[[ -n "${PHP_BIN}" ]] || PHP_BIN=php
SKIP_BACKUP="${SKIP_BACKUP:-0}"
ROLLBACK_ON_FAIL="${ROLLBACK_ON_FAIL:-1}"

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

SNAPSHOT_DIR="storage/app/upgrade-snapshots"
SNAPSHOT_FILE="${SNAPSHOT_DIR}/pre-${VERSION}-$(date +%Y%m%d_%H%M%S).json"

cd "${APP_PATH}"

echo "==> Capturing pre-upgrade snapshot for v${VERSION}"
${PHP_BIN} artisan core:snapshot --output="${SNAPSHOT_FILE}"

UPGRADE_ARGS=(artisan core:upgrade "${VERSION}" --force)

if [[ "${SKIP_BACKUP}" == "1" ]]; then
  UPGRADE_ARGS+=(--no-backup)
fi

echo "==> Upgrading core to v${VERSION}"
if ! ${PHP_BIN} "${UPGRADE_ARGS[@]}"; then
  echo "==> Upgrade failed"
  exit 1
fi

echo "==> Verifying upgrade"
if ! ${PHP_BIN} artisan core:verify \
  --expected-version="${VERSION}" \
  --compare-with="${SNAPSHOT_FILE}"; then

  echo "==> Verification failed"

  if [[ "${ROLLBACK_ON_FAIL}" == "1" ]]; then
    echo "==> Attempting rollback from latest backup"
    ${PHP_BIN} artisan core:rollback --latest --force || true
  fi

  exit 1
fi

echo "==> Upgrade to v${VERSION} verified successfully"
