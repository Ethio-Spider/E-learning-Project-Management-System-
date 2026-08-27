# Installation Guide - E-Learning Management System

This guide will help you set up the E-Learning Management System on your local machine or server.

## Prerequisites

Before you begin, ensure you have:
- PHP 8.0 or higher
- MySQL 5.7 or MariaDB 10.2+
- A text editor or IDE
- Command line access
- Web server (Apache, Nginx, or use PHP built-in server)

## Step-by-Step Installation

### Step 1: Download and Extract Files

1. Download the project files
2. Extract to your desired location:
   ```bash
   cd /path/to/your/webroot
   unzip elearning-system.zip
   cd elearning-system
   ```

### Step 2: Set Up the Database

#### Option A: Using Command Line
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE elearning_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'elearning_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON elearning_db.* TO 'elearning_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import schema
mysql -u elearning_user -p elearning_db < database_schema.sql
```

#### Option B: Using phpMyAdmin
1. Open phpMyAdmin in your browser
2. Click "New" or "Create"
3. Create database: `elearning_db`
4. Select UTF-8 collation
5. Go to Import tab
6. Upload `database_schema.sql`
7. Click Import

### Step 3: Configure Environment

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` file with your database credentials:**
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=elearning_db
   DB_USER=elearning_user
   DB_PASSWORD=secure_password
   DB_TIMEZONE=UTC
   APP_DEBUG=false
   LOG_LEVEL=INFO
   ```

### Step 4: Create Required Directories

```bash
# Create logs directory
mkdir -p logs

# Create uploads directory
mkdir -p uploads

# Set permissions (Linux/Mac)
chmod 755 logs uploads
```

On Windows, ensure these folders exist (Windows usually handles permissions automatically).

### Step 5: Configure PHP Settings (Optional)

Edit your `php.ini`:
```ini
; Recommended settings
max_upload_size = 52M
post_max_size = 52M
memory_limit = 256M
max_execution_time = 300
default_charset = "UTF-8"

; Enable error logging
error_log = logs/php-errors.log
```

### Step 6: Start the Application

#### Option A: Using PHP Built-in Server (Development)
```bash
php -S localhost:8000
```

Then open your browser to: `http://localhost:8000`

#### Option B: Using Apache (Production)
1. Configure your Apache virtual host (or use existing)
2. Point DocumentRoot to project folder
3. Ensure `mod_rewrite` is enabled:
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
4. Access via your domain: `http://your-domain.com`

#### Option C: Using Nginx
Add to your Nginx configuration:
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?/$1 last;
    }
}

location ~ \.php$ {
    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

### Step 7: Verify Installation

1. **Check database connection:**
   - Open a course management page
   - You should see sample courses

2. **Test API:**
   ```bash
   curl http://localhost:8000/api.php
   ```
   You should get a JSON response with courses.

3. **Check logs:**
   ```bash
   tail logs/$(date +%Y-%m-%d).log
   ```

## Troubleshooting

### Issue: "Cannot connect to database"
**Solution:**
- Verify MySQL is running
- Check credentials in `.env`
- Ensure database `elearning_db` exists
- Check if user has proper permissions

### Issue: "Permission denied" on logs folder
**Solution:**
```bash
# Linux/Mac
chmod 777 logs
chmod 777 uploads

# Or with web server user
sudo chown -R www-data:www-data logs uploads
```

### Issue: "404 Not Found" or blank page
**Solution:**
- Check `.htaccess` file exists
- Enable `mod_rewrite`: `a2enmod rewrite`
- Verify file permissions (755 for dirs, 644 for files)

### Issue: "Call to undefined class"
**Solution:**
- Ensure all class files exist in `classes/` directory
- Check file paths in includes
- Verify PHP can access the files

### Issue: No database tables created
**Solution:**
```bash
# Re-import the schema
mysql -u elearning_user -p elearning_db < database_schema.sql

# Or manually run from phpMyAdmin
```

## Post-Installation

### 1. Create Sample Data
The database schema includes sample courses. View them by opening the application.

### 2. Change Admin Settings
Update your `.env` file with production settings:
```env
APP_DEBUG=false
LOG_LEVEL=WARNING
ENABLE_CORS=false
CORS_ORIGINS=yourdomain.com
```

### 3. Set Up Regular Backups
```bash
# Create daily backup
mysqldump -u elearning_user -p elearning_db > backup_$(date +%Y%m%d).sql
```

### 4. Configure Email (Optional)
For enrollment notifications:
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASSWORD=app-password
EMAIL_FROM=noreply@yourdomain.com
```

### 5. Enable HTTPS
Update `.htaccess`:
```apache
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Security Checklist

- [ ] Set `APP_DEBUG=false` in production
- [ ] Create strong database password
- [ ] Restrict access to `.env` file
- [ ] Enable HTTPS on production
- [ ] Set proper file permissions (755 for dirs, 644 for files)
- [ ] Configure firewall rules
- [ ] Set up automated backups
- [ ] Monitor logs regularly
- [ ] Update PHP to latest stable version
- [ ] Keep MySQL/MariaDB updated

## Performance Optimization

### 1. Enable Database Query Caching
In MySQL:
```sql
SET GLOBAL query_cache_size = 268435456;
SET GLOBAL query_cache_type = 1;
```

### 2. Enable PHP Caching
Install and configure Memcached or Redis

### 3. Optimize CSS/JavaScript
- Minify files
- Enable gzip compression in `.htaccess`
- Use CDN for static assets

### 4. Database Optimization
```bash
# Optimize tables
OPTIMIZE TABLE projects;
OPTIMIZE TABLE enrollments;
OPTIMIZE TABLE resources;
```

## Updating the System

To update to a new version:

1. Backup your database:
   ```bash
   mysqldump -u elearning_user -p elearning_db > backup.sql
   ```

2. Back up your files:
   ```bash
   cp -r . ../elearning-backup
   ```

3. Download and extract new version

4. Run any new migrations from `database_schema.sql`:
   ```bash
   # Only run new ALTER TABLE statements
   mysql -u elearning_user -p elearning_db < updates.sql
   ```

## Support

If you encounter issues:

1. Check the logs in `logs/` folder
2. Review this installation guide
3. Check the README for API documentation
4. Review source code comments

## Getting Started

After successful installation:

1. Open the application in your browser
2. Click "Add Course" to create your first course
3. Fill in course details and save
4. View your course and try enrolling a student
5. Explore the filtering and search features

Happy learning!
