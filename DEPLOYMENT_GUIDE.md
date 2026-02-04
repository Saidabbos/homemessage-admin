# 🚀 Golden Touch - Deployment Guide

## Server Information
- **IP**: 64.226.102.231
- **Domain**: hm.make-it.uz
- **OS**: Ubuntu + Nginx
- **Access**: SSH as root

---

## Pre-Deployment Checklist

Before running deployment:

- [ ] Server IP points to domain (DNS records updated)
- [ ] SSH key added to server (`~/.ssh/authorized_keys`)
- [ ] GitHub SSH key set up on server
- [ ] This repo pushed to GitHub (or self-hosted Git)

---

## Deployment Steps

### 1. **SSH into Server**
```bash
ssh root@64.226.102.231
```

### 2. **Update GitHub URL in Script**
Before running deployment, update the Git repository URL in the script:

```bash
# On your local machine
nano deploy.sh
# Find this line and update it:
REPO_URL="git@github.com:yourusername/hm.git"
```

Then push the updated script to GitHub:
```bash
git add deploy.sh
git commit -m "Update: Production deployment script with GitHub URL"
git push origin master
```

### 3. **Download and Run Deployment Script**

On the server:
```bash
cd /tmp
git clone git@github.com:yourusername/hm.git temp-hm
cd temp-hm
bash deploy.sh hm.make-it.uz
```

Or if already cloned:
```bash
cd /var/www/hm
bash deploy.sh hm.make-it.uz
```

### 4. **Script Output**
The script will:
- ✅ Install system dependencies (PHP, MySQL, Nginx)
- ✅ Clone/pull the repository
- ✅ Install Composer dependencies
- ✅ Set up MySQL database
- ✅ Run Laravel migrations
- ✅ Install Node dependencies and build assets
- ✅ Configure PHP-FPM and Nginx
- ✅ Set up SSL certificate with Let's Encrypt
- ✅ Configure caching and optimization

---

## After Deployment

### 1. **Verify Application**
```bash
# On server
curl https://hm.make-it.uz
# Should return HTML page

# Or check in browser
https://hm.make-it.uz
```

### 2. **Check Application Logs**
```bash
tail -f /var/www/hm/storage/logs/laravel.log
```

### 3. **Verify Services**
```bash
# Check Nginx status
sudo systemctl status nginx

# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check MySQL status
sudo systemctl status mysql
```

### 4. **Access Admin Panel**
```
URL: https://hm.make-it.uz/admin/login
Default credentials: Check ADMIN_LOGIN_GUIDE.md
```

### 5. **Set Up Environment Variables**
If needed, update `/var/www/hm/.env` on server:
```bash
ssh root@64.226.102.231
nano /var/www/hm/.env
# Make changes...
# Then restart services:
systemctl restart php8.3-fpm
```

---

## Common Issues & Solutions

### Issue: "504 Gateway Timeout"
**Solution:**
```bash
# Check PHP-FPM error log
tail -f /var/log/php8.3-fpm.log

# Restart PHP-FPM
systemctl restart php8.3-fpm

# Check Laravel logs
tail -f /var/www/hm/storage/logs/laravel.log
```

### Issue: "Database connection refused"
**Solution:**
```bash
# Check MySQL is running
systemctl status mysql

# Check database credentials in .env
grep DB_ /var/www/hm/.env

# Test MySQL connection
mysql -u golden_user -p golden_touch -h 127.0.0.1
```

### Issue: "Permission denied" errors
**Solution:**
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/hm
sudo chmod -R 755 /var/www/hm
sudo chmod -R 775 /var/www/hm/storage
sudo chmod -R 775 /var/www/hm/bootstrap/cache
```

### Issue: "SSL certificate issues"
**Solution:**
```bash
# Check certificate expiration
sudo certbot certificates

# Renew manually
sudo certbot renew --non-interactive

# Check Nginx SSL config
sudo nginx -t
```

---

## Maintenance Commands

### Update Application
```bash
cd /var/www/hm
git pull origin master
composer install
php artisan migrate --force
npm install
npm run build
systemctl restart php8.3-fpm
```

### Backup Database
```bash
mysqldump -u golden_user -p golden_touch > backup_$(date +%Y%m%d_%H%M%S).sql
```

### View Real-Time Logs
```bash
# Nginx error log
tail -f /var/log/nginx/error.log

# Nginx access log
tail -f /var/log/nginx/access.log

# Laravel log
tail -f /var/www/hm/storage/logs/laravel.log
```

---

## SSL Certificate Renewal

The certificate will auto-renew via cron job. To check:
```bash
sudo certbot renew --dry-run
```

---

## Rollback to Previous Version

If deployment fails, rollback to previous commit:
```bash
cd /var/www/hm
git log --oneline -5
git checkout <commit-hash>
composer install
php artisan migrate:rollback --force
systemctl restart php8.3-fpm
```

---

## Security Hardening (Optional)

### Enable UFW Firewall
```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

### Create SSH Key for Automated Deployments
```bash
ssh-keygen -t ed25519 -C "deployment@hm.make-it.uz"
# Add public key to GitHub repository settings
```

### Disable Root Login
```bash
sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
systemctl restart ssh
```

---

## Monitoring & Alerts

### Set Up Log Rotation
```bash
# Logs are auto-rotated via Laravel + system logrotate
sudo logrotate -f /etc/logrotate.conf
```

### Monitor Disk Space
```bash
df -h
du -sh /var/www/hm/*
```

---

## Support

For issues or questions:
1. Check logs first: `tail -f /var/www/hm/storage/logs/laravel.log`
2. Review deployment script output
3. Check services: `systemctl status nginx php8.3-fpm mysql`

---

**Last Updated**: 2026-02-05
**Deployment Script Version**: 1.0
