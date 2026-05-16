@echo off
chcp 65001 > nul
title Installer Aplikasi SPJ
color 0A

echo ============================================================
echo        INSTALLER APLIKASI SPJ - SETUP OTOMATIS
echo ============================================================
echo.

:: ============================================================
:: CLONE REPOSITORY
:: ============================================================
echo [1/10] Memeriksa Git dan Mengambil Source Code...
git --version > nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Git tidak ditemukan!
    echo Pastikan Git sudah terinstall. Download dari: https://git-scm.com/downloads
    pause
    exit /b 1
)

set REPO_URL=https://github.com/Badrut/laravel-docs
set FOLDER_NAME=laravel-docs

if exist "%FOLDER_NAME%\" (
    echo [INFO] Folder '%FOLDER_NAME%' sudah ada. Melewati proses clone...
    cd "%FOLDER_NAME%"
) else (
    echo Menge-clone repositori %REPO_URL% ...
    git clone %REPO_URL% %FOLDER_NAME%
    if %errorlevel% neq 0 (
        echo [ERROR] Gagal menge-clone repositori! Pastikan koneksi internet aman.
        pause
        exit /b 1
    )
    echo [OK] Repositori berhasil di-clone.
    cd "%FOLDER_NAME%"
)

:: ============================================================
:: CEK PHP
:: ============================================================
echo.
echo [2/10] Memeriksa PHP...
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
:: KONFIGURASI LIMIT PHP.INI
:: ============================================================
echo.
echo [3/10] Mengatur Limit php.ini (Upload 500M)...
php -r "$ini = php_ini_loaded_file(); if ($ini) { $c = file_get_contents($ini); $c = preg_replace('/^;?upload_max_filesize.*/m', 'upload_max_filesize = 500M', $c); $c = preg_replace('/^;?post_max_size.*/m', 'post_max_size = 500M', $c); $c = preg_replace('/^;?memory_limit.*/m', 'memory_limit = 512M', $c); $c = preg_replace('/^;?max_execution_time.*/m', 'max_execution_time = 300', $c); file_put_contents($ini, $c); echo '[OK] Limit berhasil dinaikkan: 500M.' . PHP_EOL; } else { echo '[PERINGATAN] File php.ini tidak ditemukan, ganti manual nanti.' . PHP_EOL; }"

:: ============================================================
:: CEK COMPOSER
:: ============================================================
echo.
echo [4/10] Memeriksa Composer...
call composer -V > nul 2>&1
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
echo [5/10] Memeriksa Node.js...
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
echo [6/10] Menyiapkan file konfigurasi (.env)...
if not exist ".env" (
    copy ".env.example" ".env" > nul
    echo [OK] File .env berhasil dibuat dari .env.example
) else (
    echo [INFO] File .env sudah ada, dilewati.
)

echo ============================================================
echo  KONFIGURASI DATABASE
echo  Pastikan MySQL/MariaDB sudah berjalan di Laragon
echo ============================================================
set /p DB_NAME=Nama database [default: app_db]: 
if "%DB_NAME%"=="" set DB_NAME=app_db

set /p DB_USER=Username MySQL [default: root]: 
if "%DB_USER%"=="" set DB_USER=root

set /p DB_PASS=Password MySQL [kosongkan jika tidak ada]: 

set /p DB_PORT=Port MySQL [default: 3306]: 
if "%DB_PORT%"=="" set DB_PORT=3306

:: Update .env dengan konfigurasi database
php -r "$env = file_get_contents('.env'); $env = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=%DB_NAME%', $env); $env = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=%DB_USER%', $env); $env = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=%DB_PASS%', $env); $env = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=%DB_PORT%', $env); $env = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $env); $env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $env); file_put_contents('.env', $env); echo 'Konfigurasi .env diperbarui.' . PHP_EOL;"

:: ============================================================
:: COMPOSER INSTALL
:: ============================================================
echo.
echo [7/10] Menginstall dependensi PHP (Composer)...
echo Ini mungkin membutuhkan beberapa menit...
call composer install --no-interaction --optimize-autoloader
if %errorlevel% neq 0 (
    echo [ERROR] Composer install gagal!
    pause
    exit /b 1
)
echo [OK] Dependensi PHP berhasil diinstall.

:: ============================================================
:: GENERATE APP KEY & MIGRASI
:: ============================================================
echo.
echo [8/10] Generate Application Key & Migrasi Database...
php artisan key:generate --force
if %errorlevel% neq 0 (
    echo [ERROR] Gagal generate app key!
    pause
    exit /b 1
)

echo INFO: Jika muncul prompt "Would you like to create it?", ketik "yes" lalu Enter!
php artisan migrate:fresh --seed
if %errorlevel% neq 0 (
    echo [ERROR] Migrasi database gagal!
    echo Pastikan konfigurasi database di .env sudah benar dan MySQL berjalan.
    pause
    exit /b 1
)
echo [OK] Migrasi database berhasil.

:: ============================================================
:: STORAGE LINK & CACHE
:: ============================================================
echo.
echo [9/10] Membuat storage link dan membersihkan cache...
call php artisan storage:link --force > nul 2>&1
call php artisan optimize:clear > nul 2>&1
echo [OK] Storage dan cache siap.

:: ============================================================
:: NPM INSTALL & BUILD
:: ============================================================
if "%SKIP_NPM%"=="1" goto skip_npm
echo.
echo [10/10] Menginstall dependensi Node.js dan build aset...
call npm install --silent
call npm run build
echo [OK] Aset frontend berhasil dibuild.
goto finish_setup

:skip_npm
echo.
echo [10/10] Melewati build NPM (Node.js tidak ditemukan).

:finish_setup
:: ============================================================
:: SELESAI - JALANKAN SERVER
:: ============================================================
echo.
echo ============================================================
echo  [SETUP SELESAI!]
echo ============================================================
echo.
echo  Informasi Aplikasi:
echo  - URL     : http://localhost:8000
echo  - Database: %DB_NAME%
echo  - User DB : %DB_USER%
echo.
echo  Catatan Penting:
echo  Karena php.ini baru saja diubah, DISARANKAN untuk me-RESTART
echo  Apache/Nginx di aplikasi Laragon agar limit 500M aktif.
echo.
echo  Aplikasi akan segera berjalan...
echo  Tekan Ctrl+C untuk menghentikan server.
echo ============================================================
echo.

call php artisan serve --host=127.0.0.1 --port=8000

pause