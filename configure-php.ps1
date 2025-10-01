# Script para configurar PHP 8.2
$phpIniPath = "D:\PROGRAMAS\php\php.ini"

# Leer contenido
$content = Get-Content $phpIniPath

# Reemplazar líneas
$newContent = @()
foreach ($line in $content) {
    if ($line -match '^\s*;\s*extension_dir\s*=\s*"ext"') {
        $newContent += 'extension_dir = "D:\PROGRAMAS\php\ext"'
    }
    elseif ($line -match '^\s*;extension=openssl') {
        $newContent += 'extension=openssl'
    }
    elseif ($line -match '^\s*;extension=pdo_mysql') {
        $newContent += 'extension=pdo_mysql'
    }
    elseif ($line -match '^\s*;extension=pdo_sqlite') {
        $newContent += 'extension=pdo_sqlite'
    }
    elseif ($line -match '^\s*;extension=mbstring') {
        $newContent += 'extension=mbstring'
    }
    elseif ($line -match '^\s*;extension=fileinfo') {
        $newContent += 'extension=fileinfo'
    }
    elseif ($line -match '^\s*;extension=gd') {
        $newContent += 'extension=gd'
    }
    elseif ($line -match '^\s*;extension=zip') {
        $newContent += 'extension=zip'
    }
    elseif ($line -match '^\s*;extension=curl') {
        $newContent += 'extension=curl'
    }
    else {
        $newContent += $line
    }
}

# Guardar archivo
$newContent | Set-Content $phpIniPath

Write-Host "PHP configurado correctamente" -ForegroundColor Green
