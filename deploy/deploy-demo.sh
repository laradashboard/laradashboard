#!/usr/bin/env bash
#
# Deploy the develop branch to demo.laradashboard.com (or any staging site).
#
# Required environment variables:
#   APP_PATH   — absolute path to the Laravel application on the server
#
# Optional:
#   GIT_BRANCH      — branch to deploy (default: develop)
#   GIT_REMOTE      — remote name (default: origin)
#   PHP_BIN         — php binary (default: php)
#   COMPOSER_BIN    — composer binary (default: composer)
#   NPM_BIN         — npm binary (default: npm)
#   SKIP_NPM_BUILD  — set to 0 to run npm ci + build (default: 1)
#                     Built assets live in public/build and are committed to git.
#                     Hostinger shared hosting cannot reliably run Vite.
#
set -euo pipefail

APP_PATH="${APP_PATH:?APP_PATH is required}"
GIT_BRANCH="${GIT_BRANCH:-develop}"
GIT_REMOTE="${GIT_REMOTE:-origin}"
PHP_BIN="${PHP_BIN:-php}"
[[ -n "${PHP_BIN}" ]] || PHP_BIN=php
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
SKIP_NPM_BUILD="${SKIP_NPM_BUILD:-1}"

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

echo "==> Initializing git submodules (marketplace module)"
git submodule update --init --recursive modules/Laradashboard 2>/dev/null || true

echo "==> Deploying ${GIT_REMOTE}/${GIT_BRANCH} to ${APP_PATH}"

git fetch "${GIT_REMOTE}" --prune
git reset --hard "${GIT_REMOTE}/${GIT_BRANCH}"

${PHP_BIN} $(command -v "${COMPOSER_BIN}") install --no-dev --optimize-autoloader --no-interaction

if [[ "${SKIP_NPM_BUILD}" == "1" ]]; then
  echo "==> Skipping npm build (using committed public/build assets)"
elif [[ -f public/build/manifest.json ]]; then
  echo "==> Skipping npm build (public/build/manifest.json already present)"
else
  echo "==> Building frontend assets"
  ${NPM_BIN} ci
  ${NPM_BIN} run build
fi

${PHP_BIN} artisan migrate --force
${PHP_BIN} artisan module:publish-images
${PHP_BIN} artisan optimize:clear
${PHP_BIN} artisan optimize

echo "==> Deploy complete"
${PHP_BIN} artisan about --only=environment,cache,drivers 2>/dev/null || true
