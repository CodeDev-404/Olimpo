$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$loginPage = Invoke-WebRequest -Uri "http://127.0.0.1:8080/login" -WebSession $session -UseBasicParsing
$content = $loginPage.Content
if ($content -match 'name="_token"\s+value="([^"]+)"') {
    $token = $matches[1]
    Write-Output "Token: OK"
} else {
    Write-Output "Token: NOT FOUND"
    exit 1
}

$body = @{_token=$token; "form.email"="admin@olimpo.com"; "form.password"="admin123"; "form.remember"="on"}
try {
    $resp = Invoke-WebRequest -Uri "http://127.0.0.1:8080/login" -Method POST -Body $body -WebSession $session -MaximumRedirection 0 -ErrorAction SilentlyContinue -UseBasicParsing
} catch {
    Write-Output "Login: redirected (expected)"
}

try {
    $dash = Invoke-WebRequest -Uri "http://127.0.0.1:8080/olimpo/dashboard" -WebSession $session -UseBasicParsing -ErrorAction Stop
    Write-Output "Dashboard status: $($dash.StatusCode)"
    if ($dash.Content -match "MissingLayoutException|error|Error|NotFoundHttpException") {
        Write-Output "ERROR FOUND!"
        if ($dash.Content.Length -gt 500) {
            Write-Output $dash.Content.Substring(0, 500)
        } else {
            Write-Output $dash.Content
        }
    } else {
        Write-Output "OK - No errors!"
        if ($dash.Content -match "<title>([^<]+)</title>") {
            Write-Output "Title: $($matches[1])"
        }
        if ($dash.Content -match 'wire:snapshot') {
            Write-Output "Livewire component rendered: YES"
        }
    }
} catch {
    Write-Output "Dashboard error: $_"
}
