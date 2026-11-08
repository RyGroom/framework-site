#!/bin/sh
# Called by Claude Code PostToolUse hook after writing/editing files.
# Rebuilds SCSS when a theme SCSS file is changed.

THEME_DIR="/Applications/MAMP/htdocs/framework-site/wp-content/themes/framework"
FILE=$(jq -r '.tool_input.file_path // ""')

case "$FILE" in
  *wp-content/themes/framework/assets/scss/*.scss)
    cd "$THEME_DIR" && npm run build:scss:dev 2>/dev/null || true
    ;;
esac
