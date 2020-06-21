Install-Module powershell-yaml -Force

function Get-Trans {
    param (
        # Plugin name
        [Parameter(Mandatory = $false)]
        [string]
        $Plugin,

        # L10n key
        [Parameter(Mandatory)]
        [string]
        $Key,

        # Language
        [Parameter(Mandatory)]
        [string]
        $Lang
    )

    process {
        $realKey = $Key.Split("::")[1]
        $segment = $realKey.Split(".")
        $head = $segment[0]
        $filePath = if ($Plugin) {
            "./$Plugin/lang/$Lang/$head.yml"
        }
        else {
            "./lang/$Lang/$head.yml"
        }
        $obj = Get-Content $filePath -Raw | ConvertFrom-Yaml

        $temp = ""
        for ($i = 0; $i -lt $segment.Count; $i++) {
            if ($i -eq 0) {
                continue
            }

            if ($obj[$segment[$i]]) {
                $temp = $obj[$segment[$i]]
            }
            else {
                return $Key
            }
        }

        return $temp
    }
}

Write-Host 'Marketplace Builder'
$env:NODE_ENV = 'production'

git clone "https://github.com/bs-community/plugins-dist.git" .dist
$registry = Get-Content '.dist/registry-preview.json' | ConvertFrom-Json
$packages = $registry.packages
$plugins = Get-ChildItem -Path . -Directory -Exclude @('node_modules', '.*') | ForEach-Object { $_.Name }

yarn build

[PSCustomObject[]]$updated = @()

foreach ($plugin in $plugins) {
    Set-Location $plugin
    $manifest = Get-Content "package.json" | ConvertFrom-Json
    $version = $manifest.version

    if ($packages | Where-Object { $_.name -eq $plugin -and $_.version -eq $version }) {
        Set-Location '..'
        continue
    }

    $updated += @{
        name    = if ($manifest.title.Contains('::')) {
            Get-Trans -Key $manifest.title -Lang zh_CN
        }
        else {
            $manifest.title
        }
        version = $manifest.version
    }

    Write-Host "[$plugin] Bump to $version"

    if (Test-Path 'node_modules') {
        Remove-Item 'node_modules' -Recurse -Force
    }
    if (Test-Path '.gitignore') {
        Remove-Item '.gitignore' -Force
    }
    Get-ChildItem ./assets -Recurse | ForEach-Object {
        if ($_.Name.EndsWith('.ts')) {
            Remove-Item $_
        }
    }

    if (Test-Path 'composer.json') {
        composer install
    }

    Set-Location '..'

    zip -9 -r ".dist/${plugin}_$version.zip" $plugin
}
ConvertTo-Json $updated | Out-File -FilePath 'updated.json'

foreach ($lang in 'en', 'zh_CN') {
    $packages = $plugins | ForEach-Object {
        $manifest = Get-Content "./${_}/package.json" | ConvertFrom-Json
        $name = $manifest.name
        $version = $manifest.version
        $url = "https://cdn.jsdelivr.net/gh/bs-community/plugins-dist/${name}_$version.zip"
        [PSCustomObject]@{
            name        = $name
            version     = $version
            title       = if ($manifest.title.Contains('::')) {
                Get-Trans -Plugin $_ -Key $manifest.title -Lang $lang
            }
            else {
                $manifest.title
            }
            description = if ($manifest.title.Contains('::')) {
                Get-Trans -Plugin $_ -Key $manifest.description -Lang $lang
            }
            else {
                $manifest.description
            }
            author      = $manifest.author
            require     = $manifest.require
            dist        = [PSCustomObject]@{
                type   = 'zip'
                url    = $url
                shasum = (Get-FileHash -Path ".dist/${name}_$version.zip" -Algorithm SHA256).Hash.ToLower()
            }
        }
    }
    $registry.packages = $packages
    ConvertTo-Json $registry -Depth 10 | Out-File -FilePath ".dist/registry-preview_$lang.json"
}
Copy-Item -Path '.dist/registry-preview_zh_CN.json' -Destination '.dist/registry-preview.json'
