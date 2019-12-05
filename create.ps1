param (
    [Parameter(Mandatory, Position = 0)]
    [string]
    $Id,

    [Parameter(Mandatory)]
    [string]
    $Title,

    [Parameter(Mandatory)]
    [string]
    $Description,

    [Parameter(Mandatory)]
    [string]
    $Author,

    [Parameter(Mandatory)]
    [string]
    $Namespace,

    [Parameter()]
    [string]
    $Url,

    [Parameter()]
    [switch]
    [Alias('Views')]
    $View,

    [Parameter()]
    [switch]
    [Alias('Assets')]
    $Asset,

    [Parameter()]
    [switch]
    $Lang
)

$manifest = [PSCustomObject]@{
    name = $Id
    title = $Title
    version = '0.1.0'
    description = $Description
    author = $Author
    url = $Url
    namespace = $Namespace
    require = @{
        'blessing-skin-server' = '^5.0.0'
    }
    enchants = @{}
}

New-Item "./$Id/src" -ItemType Directory | Out-Null
ConvertTo-Json $manifest | Set-Content "./$Id/package.json"
Set-Content -Value "<?php`n`nreturn function () {};" -Path "./$Id/bootstrap.php"

if ($View) {
    New-Item "./$Id/views" -ItemType Directory | Out-Null
}

if ($Asset) {
    New-Item "./$Id/assets" -ItemType Directory | Out-Null
}

if ($Lang) {
    New-Item "./$Id/lang" -ItemType Directory | Out-Null
}
