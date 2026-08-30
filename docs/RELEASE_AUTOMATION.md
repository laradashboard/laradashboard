# Release Automation

How LaraDashboard core releases are built, deployed, and verified.

For **marketplace publishing** (internal API, `release:publish`, `ld_core_upgrades`), see the Laradashboard module:

→ [`modules/Laradashboard/docs/RELEASE_PUBLISHING.md`](../modules/Laradashboard/docs/RELEASE_PUBLISHING.md)

---

## Architecture

Release automation is split between **core** (every install) and the **Laradashboard module** (laradashboard.com only).

| Responsibility | Location |
|----------------|----------|
| Build ZIP, GitHub workflows, deploy scripts | Core repo |
| Upgrade, verify, rollback commands | Core (`app/Console/Commands/`) |
| Health check for smoke tests | Core (`GET /api/health`) |
| Publish release to marketplace DB | Laradashboard module |
| Internal release API for CI | Laradashboard module |

The Laradashboard module is a git submodule:

```bash
git clone --recurse-submodules https://github.com/laradashboard/laradashboard.git
# or after a normal clone:
./scripts/setup-laradashboard-module.sh
```

Repo: https://github.com/laradashboard/laradashboard.com

---

## Release flow

```
develop ──push──► demo.laradashboard.com     (git deploy, daily staging)
   │
   ▼
PR → main (version.json bumped)
   │
   ├── auto tag vX.Y.Z
   ├── core:zip                              (build distributable ZIP)
   ├── marketplace API                     (register release — see module doc)
   ├── upgrade test sites                  (ZIP upgrade path)
   ├── core:verify                         (modules preserved)
   └── optional production deploy          (manual approval)
```

### Branches

- **`develop`** — auto-deploys to demo on every push
- **`main`** — stable; releases are tagged from here

Create `develop` once if it does not exist:

```bash
git checkout -b develop main
git push -u origin develop
```

---

## GitHub workflows

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `deploy-demo.yml` | Push to `develop` | Deploy demo + smoke tests |
| `prepare-release.yml` | Manual | Bump version, open PR to `main` |
| `release.yml` | Push to `main` when `version.json` changes | Auto tag, ZIP, publish, upgrade tests |
| `qa-build.yml` | PR labeled "QA Ready" | Build ZIP artifact for QA |
| `ci.yml` | Push/PR to `main` | Tests |

Site matrix reference: `.github/release-sites.yml`

---

## Preparing a release

**Automatic (recommended):** bump `version.json` in your PR, merge to `main` — done.

The **Release** workflow triggers when `version.json` changes on `main`. It only runs if:

- the new version is **higher** than the previous commit (semver), and
- tag `vX.Y.Z` does not already exist

Then it automatically:

1. Creates git tag `vX.Y.Z`
2. Builds ZIP via `core:zip`
3. Creates GitHub Release with the ZIP attached
4. Publishes to marketplace
5. Runs upgrade tests

**Typical flow:**

```bash
# Option A — helper workflow opens a version-bump PR for you
# GitHub Actions → Prepare Release → patch/minor/major → merge PR

# Option B — bump manually in your develop → main PR
php scripts/bump-version.php patch
# also update CHANGELOG.md, then merge to main
```

**Manual re-run:** GitHub Actions → **Release** → Run workflow (optional `version` input to force).

Do **not** bump `version.json` on docs-only merges to `main`.

---

## Deploy scripts

All scripts expect `APP_PATH` to point at the Laravel root on the server.

### Staging deploy (git pull)

Used by demo for `develop` branch deploys:

```bash
APP_PATH=/path/to/app bash deploy/deploy-demo.sh
```

Also runs `git submodule update --init modules/Laradashboard`.

### Upgrade deploy (ZIP path)

Used during release testing and production upgrades:

```bash
APP_PATH=/path/to/app bash deploy/deploy-upgrade.sh 1.3.0
```

Runs: snapshot → `core:upgrade` → `core:verify` → rollback on failure.

### Rollback

```bash
APP_PATH=/path/to/app bash deploy/rollback.sh
```

---

## Artisan commands

### Build

```bash
php artisan core:zip
php artisan core:zip --output=/path/to/laradashboard-v1.3.0.zip
```

### Upgrade lifecycle

```bash
# Before upgrade
php artisan core:snapshot --output=storage/app/upgrade-snapshots/pre.json

# Upgrade from marketplace
php artisan core:upgrade 1.3.0 --force

# Verify modules were preserved
php artisan core:verify \
  --expected-version=1.3.0 \
  --compare-with=storage/app/upgrade-snapshots/pre.json

# Rollback
php artisan core:rollback --latest --force
```

`--no-backup` skips backup creation. Demo mode (`DEMO_MODE=true`) skips backups automatically.

### Health check

```bash
curl https://demo.laradashboard.com/api/health
```

Returns version, module count, and environment info.

---

## GitHub secrets

Add these as **Repository secrets** (Settings → Secrets and variables → Actions → Repository secrets):

| Secret | Value |
|--------|-------|
| `DEMO_SSH_HOST` | `178.16.136.122` |
| `DEMO_SSH_PORT` | `65002` (optional — defaults to 65002 in workflow) |
| `DEMO_SSH_USER` | `u769668005` |
| `DEMO_SSH_KEY` | Private key contents |
| `DEMO_APP_PATH` | App root on server |
| `DEMO_PHP_BIN` | `/opt/alt/php83/usr/bin/php` (Hostinger PHP 8.3 CLI) |

Demo deploy skips `npm run build` on the server — **built assets are committed** in `public/build/`. Run `npm run build` locally (or in CI) before pushing when you change frontend files.

Demo server `.env`:

```env
DEMO_MODE=true
APP_URL=https://demo.laradashboard.com
```

### Release workflow (marketplace)

Add these **repository secrets** when you enable automated releases to laradashboard.com:

| Secret | Required | Fallback | Purpose |
|--------|----------|----------|---------|
| `RELEASE_API_TOKEN` | Yes | — | Must match `.env` on laradashboard.com |
| `MARKETPLACE_APP_PATH` | Yes | — | App root on laradashboard.com (differs from demo path) |
| `MARKETPLACE_SSH_HOST` | No | `DEMO_SSH_HOST` | Same Hostinger account — reuse demo SSH |
| `MARKETPLACE_SSH_PORT` | No | `DEMO_SSH_PORT` (default `65002`) | SSH port |
| `MARKETPLACE_SSH_USER` | No | `DEMO_SSH_USER` | SSH user |
| `MARKETPLACE_SSH_KEY` | No | `DEMO_SSH_KEY` | SSH private key |

Optional repository **variable**: `MARKETPLACE_URL` (defaults to `https://laradashboard.com`).

laradashboard.com server `.env`:

```env
RELEASE_API_TOKEN=same-value-as-github-secret
APP_URL=https://laradashboard.com
```

### production

Optional manual production deploy via workflow dispatch. Uses `PRODUCTION_SSH_*` repository secrets when you add them later.

---

## Day-to-day workflow

1. Merge features into `develop` → demo auto-deploys
2. Test at demo.laradashboard.com
3. Bump `version.json` (+ CHANGELOG) in PR from `develop` → `main`
4. Merge to `main` → release runs automatically
5. Optionally approve production deploy (manual workflow dispatch)

---

## Troubleshooting

| Problem | What to check |
|---------|---------------|
| Deploy fails on Hostinger | SSH key, `APP_PATH`, PHP/composer versions |
| Submodule missing after clone | Run `./scripts/setup-laradashboard-module.sh` |
| Marketplace publish 401 | `RELEASE_API_TOKEN` — see module doc |
| Upgrade fails | `storage/` permissions, maintenance mode lock file |
| Verify fails `modules_preserved` | Snapshot diff; core ZIP must not include `modules/` |
| Health check fails | `APP_URL` in `.env`, site online after upgrade |

---

## Core files reference

```
config/release.php
app/Services/UpgradeSnapshotService.php
app/Http/Controllers/Api/HealthController.php
app/Console/Commands/CoreSnapshotCommand.php
app/Console/Commands/CoreUpgradeCommand.php
app/Console/Commands/CoreVerifyCommand.php
app/Console/Commands/CoreRollbackCommand.php
deploy/deploy-demo.sh
deploy/deploy-upgrade.sh
deploy/rollback.sh
scripts/bump-version.php
scripts/setup-laradashboard-module.sh
.github/release-sites.yml
.github/workflows/deploy-demo.yml
.github/workflows/release.yml
.github/workflows/prepare-release.yml
```
