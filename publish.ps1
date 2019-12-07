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

Set-Location '..'

$botRelease = (Invoke-WebRequest 'https://api.github.com/repos/bs-community/telegram-bot/releases/latest').Content | ConvertFrom-Json
$botBinUrl = ((Invoke-WebRequest $botRelease.assets_url).Content | ConvertFrom-Json).browser_download_url

bash -c "curl -fSL $botBinUrl -o bot"
chmod +x ./bot
./bot plugin updated.json
