Install-Module powershell-yaml -Force

function Get-Trans {
  param (
    # Plugin name
    [Parameter(Mandatory)]
    [string]
    $Plugin,

    # L10n key
    [Parameter(Mandatory)]
    [string]
    $Key
  )

  process {
    $realKey = $Key.Split("::")[1]
    $segment = $realKey.Split(".")
    $head = $segment[0]
    $obj = Get-Content "./$Plugin/lang/zh_CN/$head.yml" | ConvertFrom-Yaml


    $temp = ""
    for ($i = 0; $i -lt $segment.Count; $i++) {
      if ($i -eq 0) {
        continue
      }

      if ($obj[$segment[$i]]) {
        $temp = $obj[$segment[$i]]
      } else {
        return $Key
      }
    }

    return $temp
  }
}

Write-Host 'Marketplace Builder'
$env:NODE_ENV = 'production'

git clone "https://dev.azure.com/blessing-skin/Plugins/_git/Plugins" .dist
$registry = Get-Content '.dist/registry-preview.json' | ConvertFrom-Json
$packages = $registry.packages
$plugins = Get-ChildItem -Path . -Directory -Exclude @('node_modules', '.*') | ForEach-Object {$_.Name}

[PSObject[]]$updated = @()

foreach ($plugin in $plugins) {
  Set-Location $plugin
  $manifest = Get-Content "package.json" | ConvertFrom-Json
  $version = $manifest.version

  if ($packages | Where-Object {$_.name -eq $plugin -and $_.version -eq $version}) {
    Set-Location '..'
    continue
  }

  $updated += @{ name = $manifest.title; version = $manifest.version }

  Write-Host "[$plugin] Bump to $version"

  if ($manifest.scripts -and $manifest.scripts.build) {
      yarn build
  }
  if (Test-Path 'node_modules') {
      Remove-Item 'node_modules' -Recurse -Force
  }
  if (Test-Path 'assets/src') {
    Remove-Item 'assets/src' -Recurse -Force
  }
  if (Test-Path '.gitignore') {
    Remove-Item '.gitignore' -Force
  }

  if (Test-Path 'composer.json') {
    composer install
  }

  Set-Location '..'

  zip -9 -r ".dist/${plugin}_$version.zip" $plugin
}

$packages = $plugins | ForEach-Object {
  $manifest = Get-Content "./${_}/package.json" | ConvertFrom-Json
  $name = $manifest.name
  $version = $manifest.version
  @{
    name = $name
    version = $version
    title = if ($manifest.title.Contains('::')) {
      Get-Trans -Plugin $_ -Key $manifest.title
    } else {
      $manifest.title
    }
    description = if ($manifest.title.Contains('::')) {
      Get-Trans -Plugin $_ -Key $manifest.description
    } else {
      $manifest.description
    }
    author = $manifest.author
    require = $manifest.require
    dist = @{
      type = 'zip'
      url = "https://dev.azure.com/blessing-skin/0dc12c60-882a-46a2-90c6-9450490193a2/_apis/git/repositories/d5283b63-dfb0-497e-ad17-2860a547596f/Items?path=%2F${name}_$version.zip"
      shasum = (Get-FileHash -Path ".dist/${name}_$version.zip" -Algorithm SHA1).Hash.ToLower()
    }
  }
}
$registry.packages = $packages
ConvertTo-Json $registry -Depth 10 | Out-File -FilePath '.dist/registry-preview.json'
ConvertTo-Json $updated | Out-File -FilePath 'updated.json'
