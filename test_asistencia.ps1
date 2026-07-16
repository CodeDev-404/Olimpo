$ErrorActionPreference = "SilentlyContinue"
$loginPage = Invoke-WebRequest -Uri "http://127.0.0.1:8080/login" -SessionVariable session -UseBasicParsing
$content = $loginPage.Content
if ($content -match '<meta\s+name="csrf-token"\s+content="([^"]+)"') {
    $token = $matches[1]
}
$body = @{_token=$token; "form.email"="admin@olimpo.com"; "form.password"="admin123"; "form.remember"="on"}
try { Invoke-WebRequest -Uri "http://127.0.0.1:8080/login" -Method POST -Body $body -WebSession $session -MaximumRedirection 0 -UseBasicParsing } catch {}

try {
    $resp = Invoke-WebRequest -Uri "http://127.0.0.1:8080/olimpo/asistencia" -WebSession $session -UseBasicParsing -ErrorAction Stop
    Write-Output "Status: $($resp.StatusCode)"
    if ($resp.Content -match "MissingLayoutException|RouteNotFoundException|Error|Exception") {
        Write-Output "ERROR:"
        if ($resp.Content.Length -gt 1500) {
            Write-Output $resp.Content.Substring(0, 1500)
        } else {
            Write-Output $resp.Content
        }
    } else {
        if ($resp.Content.Length -gt 100) {
            Write-Output "OK - titulo: $($resp.Content.Substring(0, 200))"
        }
    }
} catch {
    Write-Output "ERROR en request: $_"
}
