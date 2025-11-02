@echo off
echo Starting Real-Time Messaging Services...
echo.
echo This will open 2 new terminal windows:
echo   1. Reverb WebSocket Server (Port 8080)
echo   2. Queue Worker (Processes broadcasts)
echo.
echo Press any key to continue...
pause >nul

echo.
echo Starting Reverb server...
start "Reverb Server" cmd /k "cd /d %~dp0 && php artisan reverb:start"

timeout /t 2 /nobreak >nul

echo Starting Queue worker...
start "Queue Worker" cmd /k "cd /d %~dp0 && php artisan queue:work --queue=default"

echo.
echo ✅ Services started!
echo.
echo IMPORTANT: Keep both windows running for real-time messaging to work.
echo.
echo To test:
echo   1. Open a conversation in 2 different browsers
echo   2. Send a message from one browser
echo   3. It should appear instantly in the other browser
echo.
pause
