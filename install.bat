@echo off
chcp 65001 > nul
title Installer Aplikasi SPJ
color 0A

echo ============================================================
echo        INSTALLER APLIKASI SPJ - SETUP OTOMATIS
echo ============================================================
echo.

:: ============================================================
:: CEK PHP
:: ============================================================
echo [1/8] Memeriksa PHP...
php -v > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan!
    echo Pastikan Laragon atau XAMPP sudah berjalan dan PHP ada di PATH.
    echo Tips: Buka Laragon ^> Menu ^> PHP ^> Tambah ke PATH
    pause
    exit /b 1
)
for /f "tokens=2 delims= " %%v in ('php -r "echo PHP_VERSION;"') do set PHP_VERSION=%%v
echo [OK] PHP ditemukan: %PHP_VERSION%

:: ============================================================
:: CEK COMPOSER
:: ============================================================
echo.
echo [2/8] Memeriksa Composer...
composer -V > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Composer tidak ditemukan!
    echo Download dari: https://getcomposer.org/download/
    pause
    exit /b 1
)
echo [OK] Composer ditemukan.

:: ============================================================
:: CEK NODE.JS
:: ============================================================
echo.
echo [3/8] Memeriksa Node.js...
node -v > nul 2>&1
if %errorlevel% neq 0 (
    echo [PERINGATAN] Node.js tidak ditemukan. Melewati build aset frontend...
    set SKIP_NPM=1
) else (
    echo [OK] Node.js ditemukan.
    set SKIP_NPM=0
)

:: ============================================================
:: SALIN .env
:: ============================================================
echo.
echo [4/8] Menyiapkan file konfigurasi (.env)...
if not exist ".env" (
    copy ".env.example" ".env" > nul
    echo [OK] File .env berhasil dibuat dari .env.example
) else (
    echo [INFO] File .env sudah ada, dilewati.
)

:: ============================================================
:: TANYA KONFIGURASI DATABASE
:: ============================================================
echo.
echo ============================================================
echo  KONFIGURASI DATABASE
echo  Pastikan MySQL/MariaDB sudah berjalan (Laragon / XAMPP)
echo ============================================================
set /p DB_NAME=Nama database [default: app_db]: 
if "%DB_NAME%"=="" set DB_NAME=app_db

set /p DB_USER=Username MySQL [default: root]: 
if "%DB_USER%"=="" set DB_USER=root

set /p DB_PASS=Password MySQL [kosongkan jika tidak ada]: 

set /p DB_PORT=Port MySQL [default: 3306]: 
if "%DB_PORT%"=="" set DB_PORT=3306

:: Update .env dengan konfigurasi database
php -r "
$env = file_get_contents('.env');
$env = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=%DB_NAME%', $env);
$env = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=%DB_USER%', $env);
$env = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=%DB_PASS%', $env);
$env = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=%DB_PORT%', $env);
$env = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $env);
$env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $env);
file_put_contents('.env', $env);
echo 'Konfigurasi database disimpan.' . PHP_EOL;
"
echo [OK] Konfigurasi database diperbarui.

:: ============================================================
:: BUAT DATABASE (jika belum ada)
:: ============================================================
echo.
echo [INFO] Mencoba membuat database '%DB_NAME%' jika belum ada...
if "%DB_PASS%"=="" (
    mysql -u %DB_USER% -P %DB_PORT% -e "CREATE DATABASE IF NOT EXISTS \`%DB_NAME%\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2> nul
) else (
    mysql -u %DB_USER% -p%DB_PASS% -P %DB_PORT% -e "CREATE DATABASE IF NOT EXISTS \`%DB_NAME%\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2> nul
)
if %errorlevel% neq 0 (
    echo [PERINGATAN] Tidak bisa membuat database otomatis.
    echo Silakan buat database '%DB_NAME%' secara manual di phpMyAdmin atau HeidiSQL.
    echo Tekan Enter setelah database dibuat...
    pause
) else (
    echo [OK] Database '%DB_NAME%' siap.
)

:: ============================================================
:: COMPOSER INSTALL
:: ============================================================
echo.
echo [5/8] Menginstall dependensi PHP (Composer)...
echo Ini mungkin membutuhkan beberapa menit...
composer install --no-interaction --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Composer install gagal!
    pause
    exit /b 1
)
echo [OK] Dependensi PHP berhasil diinstall.

:: ============================================================
:: GENERATE APP KEY
:: ============================================================
echo.
echo [6/8] Generate Application Key...
php artisan key:generate --force
if %errorlevel% neq 0 (
    echo [ERROR] Gagal generate app key!
    pause
    exit /b 1
)
echo [OK] Application key berhasil digenerate.

:: ============================================================
:: MIGRASI DATABASE
:: ============================================================
echo.
echo Menjalankan migrasi database...
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo [ERROR] Migrasi database gagal!
    echo Pastikan konfigurasi database di .env sudah benar dan MySQL berjalan.
    pause
    exit /b 1
)
echo [OK] Migrasi database berhasil.

:: ============================================================
:: STORAGE LINK
:: ============================================================
echo.
echo Membuat storage link...
php artisan storage:link --force
echo [OK] Storage link berhasil dibuat.

:: ============================================================
:: CLEAR & CACHE CONFIG
:: ============================================================
echo.
echo Membersihkan dan membuat cache konfigurasi...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo [OK] Cache dibersihkan.

:: ============================================================
:: NPM INSTALL & BUILD
:: ============================================================
if "%SKIP_NPM%"=="0" (
    echo.
    echo [7/8] Menginstall dependensi Node.js dan build aset...
    npm install --silent
    npm run build
    if %errorlevel% neq 0 (
        echo [PERINGATAN] Build aset frontend gagal, tapi aplikasi tetap bisa berjalan.
    ) else (
        echo [OK] Aset frontend berhasil dibuild.
    )
) else (
    echo.
    echo [7/8] Melewati build NPM (Node.js tidak ditemukan).
)

:: ============================================================
:: SELESAI - JALANKAN SERVER
:: ============================================================
echo.
echo ============================================================
echo  [8/8] SETUP SELESAI!
echo ============================================================
echo.
echo  Informasi Aplikasi:
echo  - URL     : http://localhost:8000
echo  - Database: %DB_NAME%
echo  - User DB : %DB_USER%
echo.
echo  Aplikasi akan segera berjalan...
echo  Tekan Ctrl+C untuk menghentikan server.
echo ============================================================
echo.

php artisan serve --host=127.0.0.1 --port=8000

pause
