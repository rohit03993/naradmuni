#!/usr/bin/env bash
# Link PHP public_html media into Next public/ so images are served by Node (fast),
# while admin PHP still goes through the fallback rewrite to PHP_ORIGIN.
set -euo pipefail
SITE="${1:-/home/thenaradmuni/htdocs/www.thenaradmuni.com}"
WEB_PUBLIC="$SITE/web/public/naradmuni"
PHP_ROOT="$SITE/public_html"

mkdir -p "$WEB_PUBLIC"
ln -sfn "$PHP_ROOT/images" "$WEB_PUBLIC/images"
# optional folders (skip if missing)
[ -d "$PHP_ROOT/team" ] && ln -sfn "$PHP_ROOT/team" "$WEB_PUBLIC/team"
[ -d "$PHP_ROOT/ads" ] && ln -sfn "$PHP_ROOT/ads" "$WEB_PUBLIC/ads"

echo "Linked:"
ls -la "$WEB_PUBLIC"
