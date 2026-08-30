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
#   UPGRADE_ZIP_PATH — use a local ZIP instead of downloading from marketplace (CI/demo)
#   MARKETPLACE_URL  — override marketplace base URL for HTTP downloads
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

artisan_has() {
  ${PHP_BIN} artisan list --raw 2>/dev/null | grep -Fxq "$1"
}

run_legacy_core_upgrade() {
  echo "==> Running legacy upgrade via CoreUpgradeService (pre core:upgrade CLI)"
  ${PHP_BIN} <<PHP
<?php
define('LARAVEL_START', microtime(true));
require '${APP_PATH}/vendor/autoload.php';
\$app = require '${APP_PATH}/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Console\Kernel::class);
\$kernel->bootstrap();

if (! class_exists(App\Services\CoreUpgradeService::class)) {
    fwrite(STDERR, "CoreUpgradeService is not available on this installation.\n");
    exit(1);
}

\$service = \$app->make(App\Services\CoreUpgradeService::class);
\$result = \$service->performUpgrade('${VERSION}', null);
\$message = \$result['message'] ?? 'Unknown upgrade result';

if (! empty(\$result['success'])) {
    fwrite(STDOUT, \$message . PHP_EOL);
    exit(0);
}

fwrite(STDERR, \$message . PHP_EOL);
exit(1);
PHP
}

verify_installed_version() {
  local installed
  installed="$(${PHP_BIN} -r "
    \$path = '${APP_PATH}/version.json';
    if (! is_file(\$path)) { exit(1); }
    \$data = json_decode(file_get_contents(\$path), true);
    echo \$data['version'] ?? '';
  " 2>/dev/null || true)"

  if [[ "${installed}" != "${VERSION}" ]]; then
    echo "==> Version mismatch: expected ${VERSION}, found ${installed:-unknown}"
    return 1
  fi

  echo "==> Verified installed version ${installed}"
}

detect_php_binary
echo "==> Using PHP: ${PHP_BIN} ($(${PHP_BIN} -r 'echo PHP_VERSION;'))"

SNAPSHOT_DIR="storage/app/upgrade-snapshots"
SNAPSHOT_FILE="${SNAPSHOT_DIR}/pre-${VERSION}-$(date +%Y%m%d_%H%M%S).json"

cd "${APP_PATH}"

if artisan_has "core:snapshot"; then
  echo "==> Capturing pre-upgrade snapshot for v${VERSION}"
  mkdir -p "${SNAPSHOT_DIR}"
  ${PHP_BIN} artisan core:snapshot --output="${SNAPSHOT_FILE}"
else
  echo "==> Skipping snapshot (core:snapshot not available on this version yet)"
  SNAPSHOT_FILE=""
fi

echo "==> Upgrading core to v${VERSION}"
if [[ -n "${UPGRADE_ZIP_PATH:-}" ]]; then
  echo "==> Using pre-staged ZIP at ${UPGRADE_ZIP_PATH}"
fi
if artisan_has "core:upgrade"; then
  UPGRADE_ARGS=(artisan core:upgrade "${VERSION}" --force)

  if [[ "${SKIP_BACKUP}" == "1" ]]; then
    UPGRADE_ARGS+=(--no-backup)
  fi

  if ! ${PHP_BIN} "${UPGRADE_ARGS[@]}"; then
    echo "==> Upgrade failed"
    exit 1
  fi
elif ! run_legacy_core_upgrade; then
  echo "==> Upgrade failed"
  exit 1
fi

echo "==> Verifying upgrade"
if artisan_has "core:verify"; then
  VERIFY_ARGS=(artisan core:verify --expected-version="${VERSION}")

  if [[ -n "${SNAPSHOT_FILE}" && -f "${SNAPSHOT_FILE}" ]]; then
    VERIFY_ARGS+=(--compare-with="${SNAPSHOT_FILE}")
  fi

  if ! ${PHP_BIN} "${VERIFY_ARGS[@]}"; then
    echo "==> Verification failed"

    if [[ "${ROLLBACK_ON_FAIL}" == "1" ]] && artisan_has "core:rollback"; then
      echo "==> Attempting rollback from latest backup"
      ${PHP_BIN} artisan core:rollback --latest --force || true
    fi

    exit 1
  fi
elif ! verify_installed_version; then
  echo "==> Verification failed"

  if [[ "${ROLLBACK_ON_FAIL}" == "1" ]] && artisan_has "core:rollback"; then
    echo "==> Attempting rollback from latest backup"
    ${PHP_BIN} artisan core:rollback --latest --force || true
  fi

  exit 1
fi

echo "==> Upgrade to v${VERSION} verified successfully"
