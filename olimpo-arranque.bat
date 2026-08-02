@echo off
cd /d C:\olimpo-web

echo [%date% %time%] Iniciando OLIMPO... >> storage\logs\server.log

start /B php artisan serve --host=0.0.0.0 --port=80
start /B php artisan queue:work --tries=1 --timeout=0
start /B php artisan schedule:work

echo OLIMPO corriendo - http://localhost:80
echo Logs: storage\logs\server.log
