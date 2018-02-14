#!/bin/bash
set -ev
export TZ='Asia/Shanghai'

cd .dist

git add .
git commit -m "Archive updated: `date +"%Y-%m-%d %H:%M:%S"`"

git push origin master:master --force --quiet
