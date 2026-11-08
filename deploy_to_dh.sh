#!/bin/bash
# Deploy the current branch to DreamHost: git push, then git pull over ssh.
# (based on dreams.robnugen.com's deploy_dreams_to_dh.sh)
#
# A site cloned from new-DH-user-site fills in DEST and DEST_PATH below and
# commits them.
#
# The template repo itself is the one exception: it must keep "example" here,
# so it hides its own filled-in copy with
#
#   git update-index --skip-worktree deploy_to_dh.sh
#
# (`--no-skip-worktree` undoes that, to commit a change to this script.)
#
# This only updates an installed site. The first install is still the manual
# `rsync -a ./ <DEST>:<DEST_PATH>/`, with .git, never --exclude .git.
set -e

DEST="example"             # an ssh Host from ~/.ssh/config
DEST_PATH="example.com"    # project root on the server, relative to ~

# The server pulls over https, so a public repo needs no GitHub key on the
# DreamHost user. A private repo sets this to "origin" and gives the server
# a deploy key.
PULL_URL=""

cd "$(dirname "$0")"

if [ "$DEST" = "example" ]; then
    echo "Set DEST and DEST_PATH in $0 first." >&2
    exit 1
fi

if [ -z "$PULL_URL" ]; then
    PULL_URL=$(git remote get-url origin | sed -E 's#^git@github\.com:#https://github.com/#')
fi

BRANCH=$(git rev-parse --abbrev-ref HEAD)

echo "Pushing $BRANCH to origin..."
git push -u origin "$BRANCH"

echo "Pulling $BRANCH on DreamHost..."
# shellcheck disable=SC2029  # expanding locally is the point
ssh "$DEST" "cd $DEST_PATH; git restore .; git pull $PULL_URL $BRANCH"

echo "Deployed $BRANCH to $DEST."
