git config --global user.name "Pig Fang"
git config --global user.email "g-plane@hotmail.com"

$env:NODE_ENV = 'production'
$token = $env:AZURE_TOKEN

git clone https://dev.azure.com/blessing-skin/Plugins/_git/Plugins .dist
node build.js
Set-Location .dist
git add .

$shouldUpdate = git status -s
if ($shouldUpdate) {
    git commit -m "Publish"
    git push -c http.extraheader="AUTHORIZATION: basic $token" origin master
}
