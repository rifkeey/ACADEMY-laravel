#!/usr/bin/env bash
# Builds an upload-ready release for GO54 shared hosting (see docs/DEPLOYMENT_GO54.md).
#
# It packages exactly what is committed (so uncommitted drafts never ship), installs production
# dependencies into a temporary copy (your local vendor/ is untouched), removes development-only files
# and writes release/amaniq-academy-<commit>.tar.gz, which cPanel File Manager can extract.
#
# Usage:  bash scripts/build-release.sh
# Override tool paths with PHP=... COMPOSER=... if they are not the defaults below.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP="${PHP:-php}"
COMPOSER="${COMPOSER:-$HOME/bin/composer.phar}"
command -v "$PHP" >/dev/null 2>&1 || { echo "PHP not found; set PHP=/path/to/php" >&2; exit 1; }
[ -f "$COMPOSER" ] || { echo "Composer not found at $COMPOSER; set COMPOSER=/path/to/composer.phar" >&2; exit 1; }

cd "$ROOT"
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "Warning: you have uncommitted changes to tracked files. They are NOT included; only the last commit is packaged." >&2
fi
COMMIT="$(git rev-parse --short HEAD)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

echo "Packaging commit $COMMIT"
git archive --format=tar HEAD | tar -x -C "$WORK"

echo "Installing production dependencies"
# --no-scripts: package discovery would boot the app, which (correctly) refuses to run in production
# without a real .env. Laravel rebuilds its package manifest automatically on the first request.
(cd "$WORK" && "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts --quiet)
rm -f "$WORK"/bootstrap/cache/*.php

# Development-only files that should never be on the server.
rm -rf "$WORK/tests" "$WORK/node_modules" "$WORK/.github" "$WORK/scripts"
rm -f "$WORK/CLAUDE.md" "$WORK/phpunit.xml" "$WORK/.env" "$WORK/vite.config.js" "$WORK/package.json" "$WORK/package-lock.json"
# Writable folders must exist but start empty.
find "$WORK/storage" -type f ! -name '.gitignore' -delete
for d in storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; do
    mkdir -p "$WORK/$d"
done

# The built CSS/JS must be present: the server cannot build assets.
[ -f "$WORK/public/build/manifest.json" ] || { echo "public/build/manifest.json is missing; run npm run build and commit public/build" >&2; exit 1; }

mkdir -p "$ROOT/release"
OUT="$ROOT/release/amaniq-academy-$COMMIT.tar.gz"
tar -czf "$OUT" -C "$WORK" .
echo "Release written to $OUT ($(du -h "$OUT" | cut -f1))"
echo "Next: follow docs/DEPLOYMENT_GO54.md from step 3 (upload outside public_html)."
