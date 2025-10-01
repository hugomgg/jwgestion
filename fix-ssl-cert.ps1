# Script para solucionar cURL error 60 - SSL certificate problem
# Este script descarga el certificado CA bundle y configura PHP para usarlo

Write-Host "==================================" -ForegroundColor Cyan
Write-Host "Solucionador de cURL SSL Error 60" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""

# 1. Detectar php.ini en uso
Write-Host "[1/5] Detectando configuración de PHP..." -ForegroundColor Yellow
$phpIniPath = php --ini | Select-String "Loaded Configuration File:" | ForEach-Object { $_.ToString().Split(":")[1].Trim() }

if (-not $phpIniPath -or -not (Test-Path $phpIniPath)) {
    Write-Host "ERROR: No se pudo encontrar php.ini" -ForegroundColor Red
    Write-Host "Ubicación esperada: $phpIniPath" -ForegroundColor Red
    exit 1
}

Write-Host "✓ php.ini encontrado: $phpIniPath" -ForegroundColor Green
Write-Host ""

# 2. Descargar certificado CA bundle
Write-Host "[2/5] Descargando certificado CA bundle desde curl.se..." -ForegroundColor Yellow
$phpDir = Split-Path -Parent $phpIniPath
$cacertPath = Join-Path $phpDir "cacert.pem"

try {
    # Descargar el bundle de certificados de confianza
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile $cacertPath
    Write-Host "✓ Certificado descargado: $cacertPath" -ForegroundColor Green
} 
catch {
    Write-Host "ERROR: No se pudo descargar el certificado" -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red
    exit 1
}
Write-Host ""

# 3. Hacer backup del php.ini
Write-Host "[3/5] Creando backup de php.ini..." -ForegroundColor Yellow
$backupPath = "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"
Copy-Item $phpIniPath $backupPath
Write-Host "✓ Backup creado: $backupPath" -ForegroundColor Green
Write-Host ""

# 4. Leer contenido actual de php.ini
Write-Host "[4/5] Actualizando configuración de php.ini..." -ForegroundColor Yellow
$phpIniContent = Get-Content $phpIniPath

# Buscar si ya existe la configuración de curl.cainfo
$curlCainfoLine = $phpIniContent | Select-String -Pattern "^\s*curl\.cainfo\s*="
$opensslCafileLine = $phpIniContent | Select-String -Pattern "^\s*openssl\.cafile\s*="

# Preparar nuevas líneas
$newCurlLine = "curl.cainfo = `"$cacertPath`""
$newOpensslLine = "openssl.cafile = `"$cacertPath`""

# Actualizar o agregar configuración
$updatedContent = @()
$curlUpdated = $false
$opensslUpdated = $false

foreach ($line in $phpIniContent) {
    if ($line -match "^\s*;?\s*curl\.cainfo\s*=") {
        # Reemplazar línea existente (comentada o no)
        $updatedContent += $newCurlLine
        $curlUpdated = $true
    } elseif ($line -match "^\s*;?\s*openssl\.cafile\s*=") {
        # Reemplazar línea existente (comentada o no)
        $updatedContent += $newOpensslLine
        $opensslUpdated = $true
    } else {
        $updatedContent += $line
    }
}

# Si no se encontraron las líneas, agregarlas al final de la sección [curl]
if (-not $curlUpdated) {
    # Buscar la sección [curl] y agregar después
    $curlSectionIndex = -1
    for ($i = 0; $i -lt $updatedContent.Count; $i++) {
        if ($updatedContent[$i] -match "^\s*\[curl\]") {
            $curlSectionIndex = $i
            break
        }
    }
    
    if ($curlSectionIndex -ge 0) {
        $updatedContent = $updatedContent[0..$curlSectionIndex] + $newCurlLine + $updatedContent[($curlSectionIndex + 1)..($updatedContent.Count - 1)]
    } else {
        # Si no existe la sección [curl], agregarla
        $updatedContent += ""
        $updatedContent += "[curl]"
        $updatedContent += $newCurlLine
    }
}

if (-not $opensslUpdated) {
    # Buscar la sección [openssl] y agregar después
    $opensslSectionIndex = -1
    for ($i = 0; $i -lt $updatedContent.Count; $i++) {
        if ($updatedContent[$i] -match "^\s*\[openssl\]") {
            $opensslSectionIndex = $i
            break
        }
    }
    
    if ($opensslSectionIndex -ge 0) {
        $updatedContent = $updatedContent[0..$opensslSectionIndex] + $newOpensslLine + $updatedContent[($opensslSectionIndex + 1)..($updatedContent.Count - 1)]
    } else {
        # Si no existe la sección [openssl], agregarla
        $updatedContent += ""
        $updatedContent += "[openssl]"
        $updatedContent += $newOpensslLine
    }
}

# Guardar el archivo actualizado
$updatedContent | Set-Content $phpIniPath -Encoding UTF8
Write-Host "✓ php.ini actualizado" -ForegroundColor Green
Write-Host "  - curl.cainfo = `"$cacertPath`"" -ForegroundColor Cyan
Write-Host "  - openssl.cafile = `"$cacertPath`"" -ForegroundColor Cyan
Write-Host ""

# 5. Verificar configuración
Write-Host "[5/5] Verificando configuración..." -ForegroundColor Yellow
$verification = php -r "echo ini_get('curl.cainfo');"
if ($verification -eq $cacertPath) {
    Write-Host "✓ Configuración verificada correctamente" -ForegroundColor Green
} else {
    Write-Host "⚠ Advertencia: La configuración no se pudo verificar" -ForegroundColor Yellow
    Write-Host "  Valor esperado: $cacertPath" -ForegroundColor Yellow
    Write-Host "  Valor obtenido: $verification" -ForegroundColor Yellow
}
Write-Host ""

# Resumen
Write-Host "==================================" -ForegroundColor Cyan
Write-Host "✓ CONFIGURACIÓN COMPLETADA" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Cambios realizados:" -ForegroundColor White
Write-Host "  1. Certificado CA descargado en: $cacertPath" -ForegroundColor Cyan
Write-Host "  2. php.ini actualizado: $phpIniPath" -ForegroundColor Cyan
Write-Host "  3. Backup creado: $backupPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANTE:" -ForegroundColor Yellow
Write-Host "  - Si estás usando PHP desde la terminal, cierra y reabre la terminal" -ForegroundColor Yellow
Write-Host "  - Si estás usando un servidor web (Apache/Nginx), reinicia el servicio" -ForegroundColor Yellow
Write-Host "  - Prueba tu aplicación Laravel ahora" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para revertir los cambios, restaura el backup:" -ForegroundColor White
Write-Host "  Copy-Item <backup> <php.ini> -Force" -ForegroundColor Cyan
Write-Host ""
