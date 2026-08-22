# Deployment Guide for LearnFlow Pro

## Pre-Deployment Checklist

- [ ] All PHP files syntax validated
- [ ] Database schema created and seeded
- [ ] config.php updated with production credentials
- [ ] SSL certificate obtained
- [ ] Backups configured
- [ ] Logging enabled
- [ ] Error handling configured
- [ ] Rate limiting enabled

## Step 1: Prepare Your Server

### Linux/Ubuntu VPS Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1
sudo apt install php8.1 php8.1-cli php8.1-fpm php8.1-mysql php8.1-curl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Nginx (optional, instead of Apache)
sudo apt install nginx -y

# Verify installations
php -v
mysql --version
```

### Create Application Directory

```bash
sudo mkdir -p /var/www/learnflow
sudo chown $USER:$USER /var/www/learnflow
cd /var/www/learnflow

# Clone your repository
git clone https://github.com/yourusername/e-learning-platform.git .
```

## Step 2: Database Configuration

### Create Database User

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE elearning_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'learnflow'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON elearning_prod.* TO 'learnflow'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Import Schema

```bash
mysql -u learnflow -p elearning_prod < database_schema.sql
```

## Step 3: PHP Configuration

### Update config.php

```php
<?php
return [
    'DATABASE' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'elearning_prod',
        'user' => 'learnflow',
        'password' => 'SecurePassword123!',
        'charset' => 'utf8mb4',
        'timezone' => '+00:00',
    ],
    'APP' => [
        'name' => 'LearnFlow Pro',
        'debug' => false,  // IMPORTANT: Disable in production
        'url' => 'https://learnflow.app',  // Your domain
    ],
    'SESSION' => [
        'lifetime' => 3600,
        'secure' => true,  // HTTPS only
        'httponly' => true,
        'samesite' => 'Lax',
    ],
];
```

### Update php.ini

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

Key settings:

```ini
# Security
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

# Performance
max_execution_time = 30
max_input_time = 60
memory_limit = 256M
post_max_size = 50M
upload_max_filesize = 50M

# Session
session.cookie_secure = 1
session.cookie_httponly = 1
session.cookie_samesite = Lax
session.gc_maxlifetime = 3600
```

## Step 4: Web Server Configuration

### Nginx Configuration

```bash
sudo nano /etc/nginx/sites-available/learnflow
```

```nginx
server {
    listen 80;
    server_name learnflow.app www.learnflow.app;
    return 301 https://$server_name$request_uri;  # Redirect to HTTPS
}

server {
    listen 443 ssl http2;
    server_name learnflow.app www.learnflow.app;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/learnflow.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/learnflow.app/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/www/learnflow;
    index index.php;

    # Logging
    access_log /var/log/nginx/learnflow-access.log;
    error_log /var/log/nginx/learnflow-error.log;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }

    location ~ ~$ {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/learnflow /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot certonly --nginx -d learnflow.app -d www.learnflow.app
```

## Step 5: File Permissions

```bash
cd /var/www/learnflow

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# Make PHP files owned by www-data
sudo chown -R www-data:www-data /var/www/learnflow

# Ensure temp directory is writable
sudo mkdir -p /var/www/learnflow/temp
sudo chown www-data:www-data /var/www/learnflow/temp
sudo chmod 755 /var/www/learnflow/temp
```

## Step 6: Database Backups

### Automated Backup Script

```bash
sudo nano /usr/local/bin/backup-learnflow.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/backups/learnflow"
DATE=$(date +%Y%m%d_%H%M%S)
MYSQL_USER="learnflow"
MYSQL_PASSWORD="SecurePassword123!"
MYSQL_DB="elearning_prod"

mkdir -p $BACKUP_DIR

mysqldump -u $MYSQL_USER -p$MYSQL_PASSWORD $MYSQL_DB | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/backup_$DATE.sql.gz"
```

### Cron Job for Daily Backup

```bash
sudo chmod +x /usr/local/bin/backup-learnflow.sh
sudo crontab -e
```

Add line:

```
0 2 * * * /usr/local/bin/backup-learnflow.sh
```

## Step 7: Monitoring & Logging

### Error Logging

```bash
sudo mkdir -p /var/log/learnflow
sudo chown www-data:www-data /var/log/learnflow
```

Update config.php:

```php
'LOG_PATH' => '/var/log/learnflow/app.log',
'DEBUG' => false,
```

### Monitor PHP-FPM

```bash
sudo systemctl status php8.1-fpm
sudo tail -f /var/log/php8.1-fpm.log
```

### Monitor Nginx

```bash
sudo systemctl status nginx
sudo tail -f /var/log/nginx/learnflow-error.log
```

## Step 8: Performance Optimization

### Enable Caching

```bash
sudo apt install redis-server -y
sudo systemctl start redis-server
```

### PHP-FPM Tuning

```bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
```

## Troubleshooting

### Check PHP Version
```bash
php -v
```

### Test Database Connection
```bash
mysql -u learnflow -p -h localhost elearning_prod -e "SELECT 1"
```

### Check Nginx Configuration
```bash
sudo nginx -t
```

### View Recent Logs
```bash
sudo tail -50 /var/log/nginx/learnflow-error.log
sudo tail -50 /var/log/php8.1-fpm.log
```

## Production Checklist

- [ ] SSL certificate installed and auto-renewal configured
- [ ] Database backups running daily
- [ ] Error logging configured
- [ ] Security headers enabled
- [ ] Rate limiting enabled
- [ ] CORS configured if needed
- [ ] API authentication working
- [ ] Session security enabled
- [ ] File upload restrictions set
- [ ] Monitoring alerts configured

## Support

For deployment issues, check:
- Nginx error logs: `/var/log/nginx/learnflow-error.log`
- PHP-FPM logs: `/var/log/php8.1-fpm.log`
- Application logs: `/var/log/learnflow/app.log`
- Database logs: `/var/log/mysql/error.log`
