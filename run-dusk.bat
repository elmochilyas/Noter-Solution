@echo off
REM Prepare and run Dusk tests
echo Setting up Dusk environment...

REM Back up .env if not already backed up
if not exist ".env.backup" copy ".env" ".env.backup"

REM Copy dusk env over .env
copy /Y ".env.dusk.local" ".env"

REM Ensure SQLite database exists
if not exist "database\dusk.sqlite" type nul > "database\dusk.sqlite"

REM Run migrations
php artisan migrate --force

REM Start PHP dev server
start /B php artisan serve --port=8000
timeout /t 3 /nobreak >nul

REM Run dusk tests
php artisan dusk %*

REM Stop PHP server
taskkill /F /IM php.exe 2>nul

REM Restore .env
if exist ".env.backup" copy /Y ".env.backup" ".env" && del ".env.backup"

echo Dusk tests complete.
