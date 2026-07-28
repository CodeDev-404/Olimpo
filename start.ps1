Write-Host "=== OLIMPO - Iniciando entorno de desarrollo ===" -ForegroundColor Cyan
Write-Host ""

# Kill any existing processes on our ports
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue

# Build assets so they work immediately even before Vite dev connects
Write-Host "[1/2] Compilando assets..." -ForegroundColor Yellow
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error compilando assets" -ForegroundColor Red
    exit 1
}

# Start Laravel dev server in background
Write-Host "[2/2] Iniciando servidor Laravel..." -ForegroundColor Yellow
$servProc = Start-Process -NoNewWindow -FilePath "php" -ArgumentList "artisan serve --port=8000" -PassThru

Write-Host ""
Write-Host "=== SERVIDOR LISTO ===" -ForegroundColor Green
Write-Host "  http://localhost:8000" -ForegroundColor Green
Write-Host ""
Write-Host "Presiona Ctrl+C para detener" -ForegroundColor Gray

# Keep script running
try {
    while ($true) {
        Start-Sleep -Seconds 1
        if ($servProc.HasExited) {
            Write-Host "Servidor Laravel se detuvo inesperadamente" -ForegroundColor Red
            break
        }
    }
} finally {
    if ($servProc -and !$servProc.HasExited) {
        $servProc.Kill()
    }
}
