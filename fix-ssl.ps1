# Solución para cURL SSL Error 60 en PHP
Write-Host "======================================" -ForegroundColor Cyan
Write-Host " Solucionador de cURL SSL Error 60" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# 1. Detectar php.ini
Write-Host "[1/4] Detectando php.ini..." -ForegroundColor Yellow
$output = php --ini 2>&1 | Out-String
$phpIniPath = ""

if ($output -match "Loaded Configuration File:\s+(.+)") {
    $phpIniPath = $matches[1].Trim()
}

if (-not $phpIniPath -or -not (Test-Path $phpIniPath)) {
    Write-Host "ERROR: No se encontró php.ini" -ForegroundColor Red
    exit 1
}

Write-Host "PHP.ini encontrado: $phpIniPath" -ForegroundColor Green
Write-Host ""

# 2. Descargar certificado
Write-Host "[2/4] Descargando certificado CA..." -ForegroundColor Yellow
$phpDir = Split-Path -Parent $phpIniPath
$cacertPath = Join-Path $phpDir "cacert.pem"

[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile $cacertPath

Write-Host "Certificado descargado en: $cacertPath" -ForegroundColor Green
Write-Host ""

# 3. Backup
Write-Host "[3/4] Creando backup..." -ForegroundColor Yellow
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "$phpIniPath.backup_$timestamp"
Copy-Item $phpIniPath $backupPath
Write-Host "Backup creado: $backupPath" -ForegroundColor Green
Write-Host ""

# 4. Actualizar php.ini
Write-Host "[4/4] Actualizando php.ini..." -ForegroundColor Yellow

$content = Get-Content $phpIniPath
$newContent = @()
$curlFound = $false
$opensslFound = $false

foreach ($line in $content) {
    if ($line -match "^;?curl\.cainfo") {
        $newContent += "curl.cainfo = `"$cacertPath`""
        $curlFound = $true
    } elseif ($line -match "^;?openssl\.cafile") {
        $newContent += "openssl.cafile = `"$cacertPath`""
        $opensslFound = $true
    } else {
        $newContent += $line
    }
}

# Agregar si no existen
if (-not $curlFound) {
    $newContent += ""
    $newContent += "[curl]"
    $newContent += "curl.cainfo = `"$cacertPath`""
}

if (-not $opensslFound) {
    $newContent += ""
    $newContent += "[openssl]"
    $newContent += "openssl.cafile = `"$cacertPath`""
}

$newContent | Set-Content $phpIniPath -Encoding UTF8

Write-Host "php.ini actualizado" -ForegroundColor Green
Write-Host ""

# Resumen
Write-Host "======================================" -ForegroundColor Cyan
Write-Host " CONFIGURACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Archivos:" -ForegroundColor White
Write-Host "  Certificado: $cacertPath" -ForegroundColor Cyan
Write-Host "  php.ini: $phpIniPath" -ForegroundColor Cyan
Write-Host "  Backup: $backupPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANTE:" -ForegroundColor Yellow
Write-Host "  - Reinicia tu terminal o servidor web" -ForegroundColor Yellow
Write-Host "  - Prueba tu aplicación Laravel" -ForegroundColor Yellow
Write-Host ""
