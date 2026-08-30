#!/usr/bin/env bash
#
# Initialize or update the LaraDashboard marketplace module submodule.
#
# Usage:
#   ./scripts/setup-laradashboard-module.sh
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MODULE_PATH="${ROOT}/modules/Laradashboard"
MODULE_URL="https://github.com/laradashboard/laradashboard.com.git"

cd "${ROOT}"

if [[ -d "${MODULE_PATH}/.git" ]]; then
  echo "==> Updating existing Laradashboard module"
  git -C "${MODULE_PATH}" fetch origin
  git -C "${MODULE_PATH}" checkout main
  git -C "${MODULE_PATH}" pull origin main
elif [[ -f "${ROOT}/.gitmodules" ]]; then
  echo "==> Initializing Laradashboard submodule"
  git submodule update --init --recursive modules/Laradashboard
else
  echo "==> Cloning Laradashboard module"
  git submodule add "${MODULE_URL}" modules/Laradashboard
fi

echo "==> Laradashboard module ready at ${MODULE_PATH}"
git -C "${MODULE_PATH}" remote -v
git -C "${MODULE_PATH}" status -sb
