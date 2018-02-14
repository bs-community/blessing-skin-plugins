#!/bin/bash
set -ev
export TZ='Asia/Shanghai'

# Avoid direct force push
git clone git@git.coding.net:printempw/bs-plugins-archive.git .deploy_git

cd .deploy_git
git checkout master
mv .git/ ../.dist/
cd ../.dist

git add .
git commit -m "Archive updated: `date +"%Y-%m-%d %H:%M:%S"`"

git push origin master:master --force --quiet
