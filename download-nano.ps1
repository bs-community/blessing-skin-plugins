$token = 'Bearer ' + $env:GITHUB_TOKEN
$headers = @{"authorization"=$token}
$nanoRelease = (Invoke-WebRequest 'https://api.github.com/repos/bs-community/nano/releases/latest' -Headers $headers).Content | ConvertFrom-Json
$nanoBinUrl = ((Invoke-WebRequest $nanoRelease.assets_url -Headers $headers).Content | ConvertFrom-Json).browser_download_url

bash -c "curl -fSL $nanoBinUrl --header 'authorization: $token' -o nano"
chmod +x ./nano
