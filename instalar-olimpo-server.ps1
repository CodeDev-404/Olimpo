#Requires -RunAsAdministrator

$ProjectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$TaskName = "OLIMPO Server"
$Port = 80

Clear-Host
Write-Host "╔══════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║       INSTALAR OLIMPO SERVER            ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# ── 1. Buscar PHP ──
Write-Host "[1/5] Buscando PHP..." -ForegroundColor Yellow
$PhpPath = "php"

# Buscar en PATH
$phpCmd = Get-Command "php.exe" -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    # Buscar en rutas comunes
    $candidates = @(
        "C:\php\php.exe",
        "C:\Program Files\php\php.exe",
        "$env:LOCALAPPDATA\Programs\php\php.exe"
    )
    foreach ($c in $candidates) {
        if (Test-Path $c) { $PhpPath = $c; $phpCmd = $true; break }
    }
}

if (-not $phpCmd) {
    Write-Host "  ⚠️  PHP no encontrado." -ForegroundColor Red
    Write-Host "  Descárgalo: https://windows.php.net/download" -ForegroundColor Yellow
    Write-Host "  (Thread Safe ZIP, PHP 8.4+, extraer a C:\php)" -ForegroundColor Yellow
    $custom = Read-Host "  O escribe la ruta a php.exe (Enter para cancelar)"
    if ([string]::IsNullOrWhiteSpace($custom)) { Write-Host "Cancelado."; exit 1 }
    if (-not (Test-Path $custom)) { Write-Host "Ruta inválida."; exit 1 }
    $PhpPath = $custom
}

php -v 2>$null | Select-String "PHP 8\.[4-9]" | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ⚠️  Se requiere PHP 8.4+" -ForegroundColor Red
    & $PhpPath -v
    exit 1
}
Write-Host "  ✓ PHP: $PhpPath" -ForegroundColor Green
& $PhpPath -v 2>&1 | Select-Object -First 1 | ForEach-Object { Write-Host "    $_" -ForegroundColor Gray }

# ── 2. Verificar extensiones ──
Write-Host "[2/5] Verificando extensiones PHP..." -ForegroundColor Yellow
$required = @("pdo_sqlite", "sqlite3", "mbstring", "gd", "fileinfo", "curl", "openssl")
$missing = @()
foreach ($ext in $required) {
    php -m 2>$null | Select-String "^$ext$" | Out-Null
    if ($LASTEXITCODE -ne 0) { $missing += $ext }
}
if ($missing.Count -gt 0) {
    Write-Host "  ⚠️  Faltan extensiones: $($missing -join ', ')" -ForegroundColor Red
    Write-Host "  Edita C:\php\php.ini y descomenta: extension=$($missing[0])" -ForegroundColor Yellow
    exit 1
}
Write-Host "  ✓ Extensiones OK" -ForegroundColor Green

# ── 3. Optimizar Laravel ──
Write-Host "[3/5] Optimizando Laravel..." -ForegroundColor Yellow
Push-Location $ProjectPath
& $PhpPath artisan config:cache 2>&1 | Out-Null
& $PhpPath artisan route:cache 2>&1 | Out-Null
& $PhpPath artisan view:cache 2>&1 | Out-Null
Pop-Location
Write-Host "  ✓ Config, rutas y vistas cacheadas" -ForegroundColor Green

# ── 4. Configurar .env ──
Write-Host "[4/5] Configurando .env..." -ForegroundColor Yellow
$envFile = Join-Path $ProjectPath ".env"
$envContent = Get-Content $envFile

# APP_URL con IP local
$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.InterfaceAlias -notlike "*Loopback*" -and
    $_.AddressFamily -eq "IPv4" -and
    $_.IPAddress -notlike "169.254.*"
} | Sort-Object PrefixOrigin -Descending | Select-Object -First 1).IPAddress

$envContent = $envContent -replace '^APP_URL=.*', "APP_URL=http://${ip}:${Port}"
$envContent = $envContent -replace '^APP_ENV=.*', 'APP_ENV=production'
$envContent = $envContent -replace '^APP_DEBUG=.*', 'APP_DEBUG=false'
$envContent | Set-Content $envFile -Encoding UTF8

Write-Host "  ✓ APP_URL=http://${ip}:${Port}" -ForegroundColor Green
Write-Host "  ✓ APP_ENV=production, APP_DEBUG=false" -ForegroundColor Green

# ── 5. Crear Tarea Programada + Firewall ──
Write-Host "[5/5] Creando servicios de Windows..." -ForegroundColor Yellow

# Firewall
$ruleName = "OLIMPO Web Server (TCP ${Port})"
Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -LocalPort $Port -Protocol TCP -Action Allow -Profile Any | Out-Null
Write-Host "  ✓ Regla de firewall creada (puerto $Port)" -ForegroundColor Green

# Tarea programada
$scriptPath = Join-Path $ProjectPath "servidor-olimpo.ps1"
$action = New-ScheduledTaskAction -Execute "powershell.exe" -Argument "-NoProfile -WindowStyle Hidden -ExecutionPolicy Bypass -File `"$scriptPath`" -PhpPath `"$PhpPath`" -Port $Port"
$trigger = New-ScheduledTaskTrigger -AtStartup
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 5 -RestartInterval (New-TimeSpan -Minutes 1) -Hidden
$principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest

Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false -ErrorAction SilentlyContinue
Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -Settings $settings -Principal $principal -Force | Out-Null

Write-Host "  ✓ Tarea '$TaskName' creada (inicio automático)" -ForegroundColor Green

# ── FIN ──
Write-Host ""
Write-Host "╔══════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║     INSTALACIÓN COMPLETADA              ║" -ForegroundColor Green
Write-Host "╚══════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""
Write-Host "  Servidor: http://${ip}:${Port}" -ForegroundColor White
Write-Host "  Los demás equipos abren http://${ip} en su navegador" -ForegroundColor White
Write-Host ""
Write-Host "  Para INICIAR ahora (sin reiniciar):" -ForegroundColor Yellow
Write-Host "    Start-ScheduledTask -TaskName '$TaskName'" -ForegroundColor White
Write-Host ""
Write-Host "  Para DETENER:" -ForegroundColor Yellow
Write-Host "    Stop-ScheduledTask -TaskName '$TaskName'" -ForegroundColor White
Write-Host ""
Write-Host "  Para DESINSTALAR:" -ForegroundColor Yellow
Write-Host "    Unregister-ScheduledTask -TaskName '$TaskName' -Confirm:`$false" -ForegroundColor White
Write-Host "    Remove-NetFirewallRule -DisplayName '$ruleName'" -ForegroundColor White
Write-Host ""
$logPath = Join-Path "storage" "logs" | Join-Path -ChildPath "server.log"
Write-Host "  Logs: $logPath" -ForegroundColor Gray
