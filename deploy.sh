#!/bin/bash

# --- ZERO-TOUCH LARAVEL/FILAMENT DEPLOYMENT SCRIPT ---
# Optimized for: Fresh Ubuntu 22.04/24.04
# Includes: PHP 8.2, MariaDB (Remote Access), Nginx, Node.js, SSL, Workers, Scheduler

set -e # Exit on error

# Ensure script is run as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (use sudo)"
  exit 1
fi

echo "--- DEPLOYMENT INITIALIZATION ---"
read -p "Enter Domain Name (e.g. emms.com): " DOMAIN
read -p "Enter Database Name: " DB_NAME
read -p "Enter Database User: " DB_USER
read -s -p "Enter Database Password: " DB_PASS
echo ""
read -s -p "Enter MariaDB Root Password: " DB_ROOT_PASS
echo ""

PROJECT_PATH=$(pwd)
PHP_VER="8.2"
APP_NAME=$(basename "$PROJECT_PATH")

echo "--- 1. Updating System & Installing Essentials ---"
apt update && apt upgrade -y
apt install -y curl git unzip zip software-properties-common nginx python3-certbot-nginx supervisor cron

echo "--- 2. Installing PHP $PHP_VER & Extensions ---"
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php$PHP_VER-fpm php$PHP_VER-cli php$PHP_VER-common php$PHP_VER-mysql \
    php$PHP_VER-sqlite3 php$PHP_VER-mbstring php$PHP_VER-xml php$PHP_VER-curl \
    php$PHP_VER-zip php$PHP_VER-redis php$PHP_VER-intl php$PHP_VER-bcmath

echo "--- 3. Installing & Securing MariaDB ---"
apt install -y mariadb-server
systemctl start mariadb

# Automated security hardening
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY '$DB_ROOT_PASS';"
mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.user WHERE User='';"
mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -u root -p"$DB_ROOT_PASS" -e "DROP DATABASE IF EXISTS test;"
mysql -u root -p"$DB_ROOT_PASS" -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -u root -p"$DB_ROOT_PASS" -e "FLUSH PRIVILEGES;"

# Enable external access (0.0.0.0)
sed -i "s/bind-address.*/bind-address = 0.0.0.0/" /etc/mysql/mariadb.conf.d/50-server.cnf
systemctl restart mariadb

# Create Project Database & User
mysql -u root -p"$DB_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
mysql -u root -p"$DB_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';"
mysql -u root -p"$DB_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';"
mysql -u root -p"$DB_ROOT_PASS" -e "FLUSH PRIVILEGES;"

echo "--- 4. Installing Composer & Node.js ---"
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

echo "--- 5. Configuring Nginx ---"
cat <<EOF > /etc/nginx/sites-available/$DOMAIN
server {
    listen 80;
    server_name $DOMAIN;
    root $PROJECT_PATH/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php$PHP_VER-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

echo "--- 6. Deploying & Configuring Application ---"
# Set initial ownership so www-data can write .env
chown -R www-data:www-data $PROJECT_PATH

if [ ! -f .env ]; then
    sudo -u www-data cp .env.example .env
fi

# Update .env with production settings
sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
sed -i "s/DB_HOST=.*/DB_HOST=127.0.0.1/" .env
sed -i "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
sed -i "s/APP_ENV=.*/APP_ENV=production/" .env
sed -i "s/APP_DEBUG=.*/APP_DEBUG=false/" .env

# Build steps as www-data to prevent permission issues
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan key:generate
sudo -u www-data php artisan migrate --force
npm install
npm run build

echo "--- 7. Production Optimizations ---"
sudo -u www-data php artisan optimize
sudo -u www-data php artisan event:cache

echo "--- 8. Setting Up Schedule (Cron) ---"
(crontab -u www-data -l 2>/dev/null; echo "* * * * * cd $PROJECT_PATH && php artisan schedule:run >> /dev/null 2>&1") | crontab -u www-data -

echo "--- 9. Setting Up Queue Workers (Supervisor) ---"
cat <<EOF > /etc/supervisor/conf.d/$APP_NAME-worker.conf
[program:$APP_NAME-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $PROJECT_PATH/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$PROJECT_PATH/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update
supervisorctl start all

echo "--- 10. Finalizing Permissions ---"
chmod -R 775 $PROJECT_PATH/storage $PROJECT_PATH/bootstrap/cache

echo "--- 11. SSL Activation (Let's Encrypt) ---"
certbot --nginx -d $DOMAIN --non-interactive --agree-tos --register-unsafely-without-email

echo "--- DEPLOYMENT COMPLETE ---"
echo "URL: https://$DOMAIN"
echo "Status: Live & Optimized"
echo "Workers: Active"
echo "Schedule: Active"
