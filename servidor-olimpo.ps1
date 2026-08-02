param(
    [string]$PhpPath = "php",
    [int]$Port = 80
)

$ProjectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$LogFile = Join-Path $ProjectPath "storage/logs/server.log"

$PID | Out-File -FilePath (Join-Path $ProjectPath "storage/logs/server.pid") -Force

function Write-Log {
    param([string]$Message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "${timestamp} - ${Message}" | Out-File -FilePath $LogFile -Encoding utf8 -Append
}

function Start-GuardedProcess {
    param([string]$Name, [string]$Arguments)
    try {
        $proc = Start-Process -FilePath $PhpPath -ArgumentList $Arguments -WorkingDirectory $ProjectPath -WindowStyle Hidden -PassThru -ErrorAction Stop
        Write-Log ("[OK] " + $Name + " iniciado (PID: " + $proc.Id + ")")
        return $proc
    } catch {
        Write-Log ("[ERROR] " + $Name + ": " + $_)
        return $null
    }
}

Write-Log "=== OLIMPO SERVER ==="
Write-Log "PHP: $PhpPath | Puerto: $Port"

$server = Start-GuardedProcess "Servidor Web" ("artisan serve --host=0.0.0.0 --port=" + $Port)
$queue  = Start-GuardedProcess "Queue Worker" "artisan queue:work --tries=1 --timeout=0"
$sched  = Start-GuardedProcess "Scheduler" "artisan schedule:work"

$restarts = @{ "Servidor" = 0; "Queue" = 0; "Scheduler" = 0 }

while ($true) {
    Start-Sleep -Seconds 15
    if (-not $server -or $server.HasExited) {
        $restarts["Servidor"]++
        Write-Log ("[REINICIO #" + $restarts["Servidor"] + "] Servidor web")
        $server = Start-GuardedProcess "Servidor Web" ("artisan serve --host=0.0.0.0 --port=" + $Port)
    }
    if (-not $queue -or $queue.HasExited) {
        $restarts["Queue"]++
        Write-Log ("[REINICIO #" + $restarts["Queue"] + "] Queue worker")
        $queue = Start-GuardedProcess "Queue Worker" "artisan queue:work --tries=1 --timeout=0"
    }
    if (-not $sched -or $sched.HasExited) {
        $restarts["Scheduler"]++
        Write-Log ("[REINICIO #" + $restarts["Scheduler"] + "] Scheduler")
        $sched = Start-GuardedProcess "Scheduler" "artisan schedule:work"
    }
}