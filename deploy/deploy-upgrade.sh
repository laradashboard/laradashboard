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
SKIP_BACKUP="${SKIP_BACKUP:-0}"
ROLLBACK_ON_FAIL="${ROLLBACK_ON_FAIL:-1}"

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
