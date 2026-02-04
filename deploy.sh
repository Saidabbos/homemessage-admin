#!/bin/bash

# Golden Touch Deployment Script
# Deploymentni server'da ishlatish uchun: bash deploy.sh hm.make-it.uz

set -e

DOMAIN="${1:-hm.make-it.uz}"
APP_PATH="/var/www/hm"
APP_USER="www-data"
REPO_URL="git@github.com:yourusername/hm.git"  # GitHub repo URL ni o'zgarti
DB_NAME="golden_touch"
DB_USER="golden_user"
DB_PASSWORD=$(openssl rand -base64 32)

echo "================================"
echo "🚀 Golden Touch Deployment Script"
echo "Domain: $DOMAIN"
echo "App Path: $APP_PATH"
echo "================================"

# 1. System updates
echo "📦 System updates..."
apt-get update
apt-get upgrade -y

# 2. Install dependencies
echo "📦 Installing dependencies..."
apt-get install -y \
    curl \
    wget \
    git \
    unzip \
    nginx \
    mysql-server \
    mysql-client \
    php-fpm \
    php-cli \
    php-common \
    php-mysql \
    php-mbstring \
    php-xml \
    php-bcmath \
    php-gd \
    php-zip \
    php-curl \
    composer \
    certbot \
    python3-certbot-nginx

# 3. Create application directory
echo "📁 Creating application directory..."
mkdir -p $APP_PATH
cd $APP_PATH

# 4. Clone repository (or update if exists)
if [ -d "$APP_PATH/.git" ]; then
    echo "📥 Updating repository..."
    cd $APP_PATH
    git pull origin master
else
    echo "📥 Cloning repository..."
    git clone $REPO_URL $APP_PATH
fi

# 5. Install PHP dependencies
echo "📦 Installing PHP dependencies..."
cd $APP_PATH
composer install --no-interaction --prefer-dist --optimize-autoloader

# 6. Generate APP_KEY if not exists
echo "🔑 Generating APP_KEY..."
if ! grep -q "APP_KEY=" .env 2>/dev/null || [ -z "$(grep 'APP_KEY=' .env | cut -d'=' -f2)" ]; then
    php artisan key:generate
fi

# 7. Create .env file if not exists
if [ ! -f .env ]; then
    echo "⚙️  Creating .env file..."
    cp .env.example .env

    # Update .env values
    sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sed -i "s|APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
    sed -i "s|DB_HOST=.*|DB_HOST=127.0.0.1|" .env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
fi

# 8. MySQL setup
echo "🗄️  Setting up MySQL..."
systemctl start mysql
systemctl enable mysql

# Check if database exists, if not create it
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF

# 9. Run migrations
echo "🗄️  Running migrations..."
cd $APP_PATH
php artisan migrate --force

# 10. Set permissions
echo "🔐 Setting file permissions..."
chown -R $APP_USER:$APP_USER $APP_PATH
chmod -R 755 $APP_PATH
chmod -R 775 $APP_PATH/storage
chmod -R 775 $APP_PATH/bootstrap/cache

# 11. Install Node dependencies and build assets
if [ -f package.json ]; then
    echo "📦 Installing Node dependencies..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
    apt-get install -y nodejs
    npm install
    npm run build
fi

# 12. Configure PHP-FPM
echo "⚙️  Configuring PHP-FPM..."
PHP_VERSION=$(php -r "echo PHP_VERSION;" | cut -d'.' -f1,2)
PHP_FPM_SOCK="/run/php/php${PHP_VERSION}-fpm.sock"

sed -i 's/^;pm.max_requests = .*/pm.max_requests = 1000/' /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
sed -i 's/^pm.max_children = .*/pm.max_children = 20/' /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
sed -i 's/^pm.start_servers = .*/pm.start_servers = 5/' /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf

systemctl restart php${PHP_VERSION}-fpm

# 13. Configure Nginx
echo "⚙️  Configuring Nginx..."
cat > /etc/nginx/sites-available/$DOMAIN << 'NGINX_CONFIG'
upstream php_backend {
    server unix:/run/php/php8.3-fpm.sock;
}

server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN_PLACEHOLDER;

    root /var/www/hm/public;
    index index.php;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php_backend;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_intercept_errors on;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~ /\.ht {
        deny all;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Performance optimizations
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 365d;
        add_header Cache-Control "public, immutable";
    }
}
NGINX_CONFIG

# Replace domain placeholder
sed -i "s|DOMAIN_PLACEHOLDER|$DOMAIN|g" /etc/nginx/sites-available/$DOMAIN

# Enable site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/$DOMAIN

# Disable default site
rm -f /etc/nginx/sites-enabled/default

# Test Nginx config
nginx -t

# 14. Setup SSL with Let's Encrypt
echo "🔒 Setting up SSL certificate..."
systemctl restart nginx

# Wait for Nginx to be ready
sleep 3

certbot certonly --nginx -d $DOMAIN --non-interactive --agree-tos --email admin@$DOMAIN -q || echo "SSL setup may need manual intervention"

# 15. Start services
echo "🚀 Starting services..."
systemctl restart nginx
systemctl restart php8.3-fpm
systemctl enable nginx
systemctl enable php8.3-fpm

# 16. Storage link
echo "📁 Creating storage symlink..."
cd $APP_PATH
php artisan storage:link --quiet 2>/dev/null || true

# 17. Cache clear
echo "🧹 Clearing caches..."
cd $APP_PATH
php artisan config:cache --quiet 2>/dev/null || true
php artisan route:cache --quiet 2>/dev/null || true
php artisan view:cache --quiet 2>/dev/null || true

echo ""
echo "================================"
echo "✅ Deployment Complete!"
echo "================================"
echo "🌐 Domain: https://$DOMAIN"
echo "📁 App Path: $APP_PATH"
echo "🗄️  Database: $DB_NAME"
echo "👤 DB User: $DB_USER"
echo "🔑 DB Password: $DB_PASSWORD (Keep it safe!)"
echo ""
echo "Next steps:"
echo "1. Update GitHub repo URL in deploy.sh (REPO_URL)"
echo "2. Configure .env variables as needed"
echo "3. Test application at https://$DOMAIN"
echo "4. Check logs: tail -f $APP_PATH/storage/logs/laravel.log"
echo "================================"
