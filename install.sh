#!/bin/bash

# ============================================================
#   INSTALLER APLIKASI SPJ - SETUP OTOMATIS (Linux/Mac)
# ============================================================

set -e

# Warna output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

STEP=0
TOTAL=8

step() {
    STEP=$((STEP + 1))
    echo -e "\n${CYAN}[$STEP/$TOTAL]${NC} $1"
}

ok() {
    echo -e "  ${GREEN}[OK]${NC} $1"
}

warn() {
    echo -e "  ${YELLOW}[PERINGATAN]${NC} $1"
}

err() {
    echo -e "  ${RED}[ERROR]${NC} $1"
}

echo ""
echo -e "${BLUE}============================================================${NC}"
echo -e "${BLUE}       INSTALLER APLIKASI SPJ - SETUP OTOMATIS              ${NC}"
echo -e "${BLUE}============================================================${NC}"
echo ""

# ============================================================
# [1] CEK PHP
# ============================================================
step "Memeriksa PHP..."
if ! command -v php &>/dev/null; then
    err "PHP tidak ditemukan! Install PHP 8.2+ terlebih dahulu."
    exit 1
fi
PHP_VERSION=$(php -r "echo PHP_VERSION;")
ok "PHP ditemukan: $PHP_VERSION"

# ============================================================
# [2] CEK COMPOSER
# ============================================================
step "Memeriksa Composer..."
if ! command -v composer &>/dev/null; then
    err "Composer tidak ditemukan! Install dari: https://getcomposer.org"
    exit 1
fi
ok "Composer ditemukan."

# ============================================================
# [3] CEK NODE.JS
# ============================================================
step "Memeriksa Node.js..."
SKIP_NPM=0
if ! command -v node &>/dev/null; then
    warn "Node.js tidak ditemukan. Melewati build aset frontend."
    SKIP_NPM=1
else
    ok "Node.js ditemukan: $(node -v)"
fi

# ============================================================
# [4] KONFIGURASI .env
# ============================================================
step "Menyiapkan file konfigurasi (.env)..."
if [ ! -f ".env" ]; then
    cp ".env.example" ".env"
    ok "File .env berhasil dibuat dari .env.example"
else
    ok "File .env sudah ada, dilewati."
fi

# Tanya konfigurasi database
echo ""
echo -e "${BLUE}============================================================${NC}"
echo -e " KONFIGURASI DATABASE"
echo -e " Pastikan MySQL/MariaDB sudah berjalan"
echo -e "${BLUE}============================================================${NC}"
read -p " Nama database [default: app_db]: " DB_NAME
DB_NAME=${DB_NAME:-app_db}

read -p " Username MySQL [default: root]: " DB_USER
DB_USER=${DB_USER:-root}

read -s -p " Password MySQL [kosongkan jika tidak ada]: " DB_PASS
echo ""

read -p " Port MySQL [default: 3306]: " DB_PORT
DB_PORT=${DB_PORT:-3306}

# Update .env
php -r "
\$env = file_get_contents('.env');
\$env = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=$DB_NAME', \$env);
\$env = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=$DB_USER', \$env);
\$env = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=$DB_PASS', \$env);
\$env = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=$DB_PORT', \$env);
\$env = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=127.0.0.1', \$env);
\$env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', \$env);
file_put_contents('.env', \$env);
"
ok "Konfigurasi database diperbarui."

# ============================================================
# BUAT DATABASE
# ============================================================
echo ""
echo -e "  Mencoba membuat database '$DB_NAME'..."
if [ -z "$DB_PASS" ]; then
    mysql -u "$DB_USER" -P "$DB_PORT" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null && ok "Database '$DB_NAME' siap." || warn "Tidak bisa membuat database otomatis. Buat manual via phpMyAdmin lalu tekan Enter."
else
    mysql -u "$DB_USER" -p"$DB_PASS" -P "$DB_PORT" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null && ok "Database '$DB_NAME' siap." || warn "Tidak bisa membuat database otomatis. Buat manual via phpMyAdmin lalu tekan Enter."
fi

# ============================================================
# [5] COMPOSER INSTALL
# ============================================================
step "Menginstall dependensi PHP (Composer)..."
echo "  Ini mungkin membutuhkan beberapa menit..."
composer install --no-interaction --optimize-autoloader
ok "Dependensi PHP berhasil diinstall."

# ============================================================
# [6] APP KEY & ARTISAN COMMANDS
# ============================================================
step "Menjalankan setup Laravel..."

echo "  Generate Application Key..."
php artisan key:generate --force
ok "Application key berhasil digenerate."

echo "  Migrasi database..."
php artisan migrate --force
ok "Migrasi database berhasil."

echo "  Membuat storage link..."
php artisan storage:link --force
ok "Storage link berhasil dibuat."

echo "  Membersihkan cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
ok "Cache dibersihkan."

# ============================================================
# [7] NPM BUILD
# ============================================================
step "Build aset frontend..."
if [ "$SKIP_NPM" -eq 0 ]; then
    npm install --silent
    npm run build
    ok "Aset frontend berhasil dibuild."
else
    warn "Dilewati (Node.js tidak ditemukan)."
fi

# ============================================================
# [8] JALANKAN SERVER
# ============================================================
step "SETUP SELESAI! Menjalankan server..."
echo ""
echo -e "${GREEN}============================================================${NC}"
echo -e "${GREEN}  APLIKASI BERHASIL DISETUP!${NC}"
echo -e "${GREEN}============================================================${NC}"
echo ""
echo -e "  URL Aplikasi : ${CYAN}http://localhost:8000${NC}"
echo -e "  Database     : ${CYAN}$DB_NAME${NC}"
echo -e "  User DB      : ${CYAN}$DB_USER${NC}"
echo ""
echo -e "  Tekan ${RED}Ctrl+C${NC} untuk menghentikan server."
echo -e "${GREEN}============================================================${NC}"
echo ""

php artisan serve --host=127.0.0.1 --port=8000
