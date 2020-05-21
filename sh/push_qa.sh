# !/bin/sh
# 快速将dev合并到release并推送到远程库

# 更新dev到最新
git checkout develop
git pull origin develop
git push origin develop

# 合并dev到release
git checkout release
git pull origin release
git merge develop -m 'merge from dev'

# 将release推到origin
git push origin release

# 切换回dev
git checkout develop
