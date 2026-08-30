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
#   SKIP_NPM_BUILD  — set to 1 to skip npm build (default: 0)
#
set -euo pipefail

APP_PATH="${APP_PATH:?APP_PATH is required}"
GIT_BRANCH="${GIT_BRANCH:-develop}"
GIT_REMOTE="${GIT_REMOTE:-origin}"
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"
NPM_BIN="${NPM_BIN:-npm}"
SKIP_NPM_BUILD="${SKIP_NPM_BUILD:-0}"

cd "${APP_PATH}"

echo "==> Initializing git submodules (marketplace module)"
git submodule update --init --recursive modules/Laradashboard 2>/dev/null || true

echo "==> Deploying ${GIT_REMOTE}/${GIT_BRANCH} to ${APP_PATH}"

git fetch "${GIT_REMOTE}" --prune
git reset --hard "${GIT_REMOTE}/${GIT_BRANCH}"

${COMPOSER_BIN} install --no-dev --optimize-autoloader --no-interaction

if [[ "${SKIP_NPM_BUILD}" != "1" ]]; then
  ${NPM_BIN} ci
  ${NPM_BIN} run build
fi

${PHP_BIN} artisan migrate --force
${PHP_BIN} artisan optimize:clear
${PHP_BIN} artisan optimize

echo "==> Deploy complete"
${PHP_BIN} artisan about --only=environment,cache,drivers 2>/dev/null || true
