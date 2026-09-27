#!/usr/bin/env bash
# PharmaC — provision an Oracle Cloud "Always Free" Ubuntu 22.04 VM (ARM or AMD) to run
# this Laravel 8 + MySQL app. Idempotent enough to re-run after a mistake.
#
#   export REPO_URL=git@github.com:ImGsus/PharmaC.git      # or https://… (private repos: use a deploy key)
#   sudo -E bash deploy/oracle/setup.sh pharmac.duckdns.org you@example.com
#
set -euo pipefail

DOMAIN="${1:?usage: setup.sh <fqdn> <email> [db_name] [db_user]}"
EMAIL="${2:?usage: setup.sh <fqdn> <email> [db_name] [db_user]}"
DB_NAME="${3:-pharmacy}"
DB_USER="${4:-pharmac}"
REPO_URL="${REPO_URL:?set REPO_URL to your git repository}"
APP_DIR=/var/www/pharmac
PHP=php8.2
DB_PASS="$(openssl rand -hex 16)"

export DEBIAN_FRONTEND=noninteractive

echo "==> system packages"
apt-get update -y
apt-get upgrade -y
apt-get install -y curl wget git unzip gnupg ca-certificates lsb-release \
  software-properties-common nginx mysql-server

echo "==> swap (Laravel + MySQL are tight on the 1 GB micro shapes)"
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

echo "==> PHP $PHP (Laravel 8 is EOL: stay on 8.2, which this code already runs on)"
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y $PHP-fpm $PHP-cli $PHP-mysql $PHP-mbstring $PHP-xml $PHP-curl \
  $PHP-zip $PHP-gd $PHP-intl $PHP-bcmath $PHP-opcache

cat > /etc/php/8.2/fpm/conf.d/99-pharmac.ini <<'INI'
upload_max_filesize = 128M
post_max_size = 160M
memory_limit = 512M
max_execution_time = 300
max_input_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.validate_timestamps = 0
INI
cat > /etc/php/8.2/cli/conf.d/99-pharmac.ini <<'INI'
memory_limit = 1024M
max_execution_time = 0
INI

echo "==> database"
mysql -uroot <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "==> composer"
if [ ! -x /usr/local/bin/composer ]; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> app code"
[ -d "$APP_DIR/.git" ] || git clone "$REPO_URL" "$APP_DIR"
cd "$APP_DIR"
git pull --ff-only || true
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> .env"
if [ ! -f .env ]; then
  cp deploy/oracle/env.production.example .env
  # paste the APP_KEY from your dev .env here if encrypted columns must stay readable
  php artisan key:generate --force
fi
sed -i "s#^APP_URL=.*#APP_URL=https://${DOMAIN}#"      .env
sed -i "s#^ASSET_URL=.*#ASSET_URL=https://${DOMAIN}#"  .env
sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_NAME}#"     .env
sed -i "s#^DB_USERNAME=.*#DB_USERNAME=${DB_USER}#"     .env
sed -i "s#^DB_PASSWORD=.*#DB_PASSWORD=${DB_PASS}#"     .env

mkdir -p public/storage storage/app/backups storage/app/backup-temp
php artisan storage:link 2>/dev/null || true   # not needed: config/filesystems.php uses public/storage
php artisan migrate --force

echo "==> permissions"
chown -R www-data:www-data storage bootstrap/cache public/storage
chmod -R ug+rwx storage bootstrap/cache public/storage

echo "==> caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> scheduler (products:mark-expired, daily)"
( crontab -l 2>/dev/null | grep -v 'schedule:run' ; \
  echo "* * * * * cd ${APP_DIR} && /usr/bin/${PHP} artisan schedule:run >> /dev/null 2>&1" ) | crontab -

echo "==> nginx"
install -m 0644 deploy/oracle/nginx-pharmac.conf /etc/nginx/sites-available/pharmac
sed -i "s/example.duckdns.org/${DOMAIN}/g" /etc/nginx/sites-available/pharmac
ln -sfn /etc/nginx/sites-available/pharmac /etc/nginx/sites-enabled/pharmac
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo "==> HTTPS"
apt-get install -y certbot python3-certbot-nginx
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect || \
  echo "!! certbot failed: open 80/443 in the VCN security list AND the instance firewall, then re-run"

systemctl restart "php8.2-fpm"

cat <<DONE

PharmaC should now answer on https://${DOMAIN}

Saved credentials (copy them somewhere safe, then clear this from your shell history):
  DB name      : ${DB_NAME}
  DB user      : ${DB_USER}
  DB password  : ${DB_PASS}

Next: restore your data — see deploy/README.md section 5.
DONE
