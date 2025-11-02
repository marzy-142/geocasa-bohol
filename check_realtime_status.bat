@echo off
title Real-Time Messaging - Quick Status Check
color 0E

echo.
echo ================================================
echo   REAL-TIME MESSAGING - STATUS CHECK
echo ================================================
echo.

echo [1/3] Checking if Reverb server is running...
netstat -ano | findstr ":8080" | findstr "LISTENING" >nul 2>&1
if %errorlevel% equ 0 (
    echo    ✓ Reverb is RUNNING on port 8080
    set REVERB_OK=1
) else (
    echo    ✗ Reverb is NOT running
    echo      Start it with: php artisan reverb:start
    set REVERB_OK=0
)

echo.
echo [2/3] Checking compiled assets...
if exist "public\build\manifest.json" (
    echo    ✓ Assets are compiled
    set ASSETS_OK=1
) else (
    echo    ✗ Assets not compiled
    echo      Run: npm run build
    set ASSETS_OK=0
)

echo.
echo [3/3] Checking MessageSent event configuration...
findstr /C:"ShouldBroadcastNow" "app\Events\MessageSent.php" >nul 2>&1
if %errorlevel% equ 0 (
    echo    ✓ MessageSent uses ShouldBroadcastNow (instant broadcast)
    set EVENT_OK=1
) else (
    echo    ✗ MessageSent not configured for instant broadcast
    set EVENT_OK=0
)

echo.
echo ================================================
echo   SUMMARY
echo ================================================
echo.

if "%REVERB_OK%"=="1" if "%ASSETS_OK%"=="1" if "%EVENT_OK%"=="1" (
    color 0A
    echo    ✓✓✓ ALL SYSTEMS READY ✓✓✓
    echo.
    echo    Real-time messaging should work!
    echo.
    echo    TO TEST:
    echo    1. Open http://localhost/conversations/5 in 2 browsers
    echo    2. Login as different users in each
    echo    3. Send messages - they should appear instantly!
    echo    4. Press F12 to see console logs
    echo.
) else (
    color 0C
    echo    ⚠ SOME ISSUES DETECTED ⚠
    echo.
    if "%REVERB_OK%"=="0" (
        echo    → Start Reverb: php artisan reverb:start
    )
    if "%ASSETS_OK%"=="0" (
        echo    → Build assets: npm run build
    )
    if "%EVENT_OK%"=="0" (
        echo    → Check app\Events\MessageSent.php configuration
    )
    echo.
)

echo Press any key to exit...
pause >nul
