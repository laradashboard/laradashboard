#!/bin/bash
FILE="module_statuses.json"

# Only run if inside a Git repo and file exists
if [ -f "$FILE" ] && [ -d .git ]; then
    git update-index --skip-worktree "$FILE"
    echo "[INFO] Local protection applied to $FILE"
else
    echo "[INFO] Skipping protection for $FILE (not found or not a Git repo)"
fi