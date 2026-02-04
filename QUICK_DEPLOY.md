# ⚡ Quick Deploy - 5 Minute Setup

## One-Liner Deployment

From your local machine:
```bash
# 1. Update deploy.sh with your GitHub repo URL
sed -i 's|git@github.com:yourusername/hm.git|git@github.com:YOUR_GITHUB_USERNAME/hm.git|' deploy.sh

# 2. Commit changes
git add deploy.sh DEPLOYMENT_GUIDE.md QUICK_DEPLOY.md
git commit -m "feat: Add production deployment scripts"
git push origin master

# 3. Deploy to server (run this on your local machine)
ssh root@64.226.102.231 'bash -s < <(curl -fsSL https://raw.githubusercontent.com/YOUR_GITHUB_USERNAME/hm/master/deploy.sh)' hm.make-it.uz
```

---

## Manual Steps on Server

```bash
# SSH into server
ssh root@64.226.102.231

# Clone repo to /var/www/hm
mkdir -p /var/www
git clone git@github.com:YOUR_GITHUB_USERNAME/hm.git /var/www/hm

# Run deployment
cd /var/www/hm
bash deploy.sh hm.make-it.uz

# Wait for completion...
# Then verify:
curl https://hm.make-it.uz
```

---

## After Successful Deployment

```bash
# SSH into server
ssh root@64.226.102.231

# Check status
systemctl status nginx php8.3-fpm mysql

# View logs
tail -f /var/www/hm/storage/logs/laravel.log

# Access application
# Browser: https://hm.make-it.uz
```

---

## Database Credentials (Auto-Generated)

After deployment, credentials are in `/var/www/hm/.env`:
- **Database**: golden_touch
- **User**: golden_user
- **Password**: Random (32 chars) - check .env file

---

## Troubleshooting

### 504 Gateway Timeout?
```bash
systemctl restart php8.3-fpm
tail -f /var/log/php8.3-fpm.log
```

### Database Error?
```bash
systemctl restart mysql
mysql -u root -p -e "SHOW DATABASES;"
```

### Static Assets Not Loading?
```bash
cd /var/www/hm
npm run build
systemctl restart nginx
```

---

## Update After Initial Deploy

```bash
# SSH into server
ssh root@64.226.102.231

# Pull latest code
cd /var/www/hm
git pull origin master

# Install/update dependencies
composer install
npm install && npm run build

# Run any new migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache

# Restart services
systemctl restart php8.3-fpm
```

---

**See DEPLOYMENT_GUIDE.md for detailed instructions**
