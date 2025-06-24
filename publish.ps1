if (!(Test-Path updated.json)) {
    exit
}

$token = $env:GH_TOKEN
$slug = $env:GH_APP_SLUG

$app = Invoke-RestMethod -Uri "https://api.github.com/users/$slug[bot]"

git config --global user.name $app.login
git config --global user.email "$($app.id)+$($app.login)[bot]@users.noreply.github.com"

Set-Location .dist
git add .

$shouldUpdate = git status -s
if ($shouldUpdate) {
    if ($env:AWS_ACCESS_KEY_ID -and $env:AWS_SECRET_ACCESS_KEY -and $env:AWS_DEFAULT_REGION) {
        aws s3 sync . s3://plugins-dist/ --exclude ".git/*"
    }
    git commit -m "Publish"
    git remote set-url origin "https://x-access-token:$token@github.com/bs-community/plugins-dist.git"
    git push origin master
}

Set-Location '..'

$botRelease = Invoke-RestMethod -Uri 'https://api.github.com/repos/bs-community/telegram-bot/releases/latest'
$botBinUrl = (Invoke-RestMethod -Uri $botRelease.assets_url).browser_download_url

bash -c "curl -fSL $botBinUrl -o bot"
chmod +x ./bot
./bot plugin updated.json

foreach ($lang in 'en', 'zh_CN') {
    Invoke-WebRequest "https://purge.jsdelivr.net/gh/bs-community/plugins-dist@latest/registry_$lang.json"
}
Invoke-WebRequest 'https://purge.jsdelivr.net/gh/bs-community/plugins-dist@latest/registry.json'
