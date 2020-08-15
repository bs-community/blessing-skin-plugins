if (!(Test-Path updated.json)) {
    exit
}

git config --global user.name 'Pig Fang'
git config --global user.email 'g-plane@hotmail.com'

$token = $env:GH_TOKEN

Set-Location .dist
git add .

$shouldUpdate = git status -s
if ($shouldUpdate) {
  git commit -m "Publish"
  git remote set-url origin "https://tadaf:$token@github.com/bs-community/plugins-dist.git"
  git push origin master
}

Set-Location '..'

$botRelease = (Invoke-WebRequest 'https://api.github.com/repos/bs-community/telegram-bot/releases/latest').Content | ConvertFrom-Json
$botBinUrl = ((Invoke-WebRequest $botRelease.assets_url).Content | ConvertFrom-Json).browser_download_url

bash -c "curl -fSL $botBinUrl -o bot"
chmod +x ./bot
./bot plugin updated.json

foreach ($lang in 'en', 'zh_CN') {
    Invoke-WebRequest "https://purge.jsdelivr.net/gh/bs-community/plugins-dist@latest/registry_$lang.json"
}
Invoke-WebRequest 'https://purge.jsdelivr.net/gh/bs-community/plugins-dist@latest/registry.json'
