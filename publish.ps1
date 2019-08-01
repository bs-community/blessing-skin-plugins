git config --global user.name 'Pig Fang'
git config --global user.email 'g-plane@hotmail.com'

$token = $env:AZURE_TOKEN

Set-Location .dist
git add .

$shouldUpdate = git status -s
if ($shouldUpdate) {
  git commit -m "Publish"
  git push "https://anything:$token@dev.azure.com/blessing-skin/Plugins/_git/Plugins" master
}
