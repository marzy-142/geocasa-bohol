@echo off
title Real-Time Messaging Monitor
color 0A

:monitor
cls
echo ========================================
echo   REAL-TIME MESSAGING STATUS
echo ========================================
echo.
echo Checking services...
echo.

REM Check if Reverb is listening on port 8080
netstat -ano | findstr ":8080" >nul 2>&1
if %errorlevel% equ 0 (
    echo [✓] Reverb Server: RUNNING on port 8080
) else (
    echo [✗] Reverb Server: NOT RUNNING
    echo     Start with: php artisan reverb:start
)

echo.

REM Check for queue worker processes
tasklist /FI "WINDOWTITLE eq Queue Worker*" 2>nul | findstr "cmd.exe" >nul
if %errorlevel% equ 0 (
    echo [✓] Queue Worker: RUNNING
) else (
    echo [✗] Queue Worker: NOT RUNNING
    echo     Start with: php artisan queue:work
)

echo.
echo ========================================
echo.

REM Check queue status
php artisan queue:monitor 2>nul
if %errorlevel% neq 0 (
    echo Checking queue manually...
    php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); $pending = DB::table('jobs')->count(); $failed = DB::table('failed_jobs')->count(); echo 'Pending jobs: ' . $pending . PHP_EOL; echo 'Failed jobs: ' . $failed . PHP_EOL;"
)

echo.
echo Press Ctrl+C to exit, or wait for auto-refresh...
echo.
timeout /t 10 /nobreak
goto monitor
