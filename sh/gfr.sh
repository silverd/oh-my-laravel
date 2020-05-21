#!/bin/sh
branch=$1
git fetch origin $branch
git rebase origin/$branch
