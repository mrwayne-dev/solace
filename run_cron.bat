@echo off
set PHP_PATH=C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe
set SCRIPT_PATH=D:\mrwayne\web_dev\healthruncare\api\cron\maintenance_cron.php
set LOG_PATH=D:\mrwayne\web_dev\healthruncare\api\cron\cron_log.txt

echo Starting maintenance_cron Cron... >> "%LOG_PATH%"
echo [%date% %time%] ------------------------------------------ >> "%LOG_PATH%"

"%PHP_PATH%" "%SCRIPT_PATH%" >> "%LOG_PATH%" 2>&1

echo [%date% %time%] ✅ maintenance_cron execution finished. >> "%LOG_PATH%"
echo ------------------------------------------ >> "%LOG_PATH%"
pause
