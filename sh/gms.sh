#!/bin/sh
branch=$1
msg=$2

if [ -z $msg ]
then
    msg="squash merged from ${branch}"
fi

git fetch origin $branch
git checkout develop
git pull origin develop
git merge origin/${branch} --squash
git add .
git commit -m "${msg}"
git push origin develop

# 删除源分支
git branch -D ${branch}
git push origin :${branch}
git remote prune origin
