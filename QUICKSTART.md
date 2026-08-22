# Quick Start Guide - LearnFlow Pro

## 5-Minute Local Setup

### Prerequisites
- PHP 8.1 or higher
- MySQL 5.7 or higher
- Git (optional)

### Step 1: Download Project

```bash
# Option A: Clone repository
git clone https://github.com/yourusername/e-learning-platform.git
cd E-learning-Project-Management-System--main

# Option B: Extract ZIP file
unzip E-learning-Project-Management-System--main.zip
cd E-learning-Project-Management-System--main
```

### Step 2: Setup Database

```bash
# Create database
mysql -u root -p
```

In MySQL prompt:
```sql
CREATE DATABASE elearning_db;
EXIT;
```

Import schema:
```bash
mysql -u root -p elearning_db < database_schema.sql
```

### Step 3: Configure Application

Open `config.php` and verify settings:

```php
'DATABASE' => [
    'host' => 'localhost',      // Your MySQL host
    'port' => 3306,             // Your MySQL port
    'name' => 'elearning_db',   // Database name
    'user' => 'root',           // MySQL user
    'password' => '',           // MySQL password (empty for local)
],
```

### Step 4: Start PHP Server

```bash
# From project root directory
php -S localhost:8000

# Or specific port
php -S localhost:9000
```

### Step 5: Access Application

Open browser and visit:
```
http://localhost:8000
```

## Demo Credentials

Login with any of these demo accounts:

**Student Account:**
- Email: `student@learnflow.app`
- Password: `student123`
- Role: Student

**Instructor Account:**
- Email: `instructor@learnflow.app`
- Password: `instructor123`
- Role: Instructor

**Admin Account:**
- Email: `admin@learnflow.app`
- Password: `admin123`
- Role: Admin

## First-Time Activities

### As Student
1. Log in with student credentials
2. Browse courses in "Courses" tab
3. Click "Enroll" to join a course
4. View enrolled courses in "My Courses"
5. Complete assignments in "Assignments" tab
6. View feedback after instructor grades

### As Instructor
1. Log in with instructor credentials
2. Click "Manage" to see taught courses
3. View student progress and analytics
4. Navigate to "Grading" to review submissions
5. Add feedback and scores to assignments

### As Admin
1. Log in with admin credentials
2. View platform-wide statistics
3. Monitor all courses and enrollments
4. Access system health information

## Key Features to Test

### 1. Course Enrollment
```
Student → Courses → Click any course → Enroll
```

### 2. Assignment Submission
```
Student → My Courses → Select course → View Assignments → Submit
```

### 3. Grading (Instructor)
```
Instructor → Manage → Select course → View Submissions → Grade
```

### 4. Certificate Verification
```
Student → Dashboard → View Certificates → Verify (public URL)
```

### 5. Search Functionality
```
Courses tab → Search by title, category, or level
```

## Troubleshooting

### "Connection refused" Error
**Problem:** Cannot connect to database
```bash
# Solution: Check MySQL is running
mysql -u root -p -e "SELECT 1"
```

### "No such file or directory" in database_schema.sql
```bash
# Solution: Ensure you're in correct directory
cd path/to/E-learning-Project-Management-System--main
mysql -u root -p elearning_db < database_schema.sql
```

### "Undefined variable" Warnings
**Problem:** PHP warnings in console
**Solution:** Disable warnings locally by setting in config.php:
```php
'APP' => ['debug' => false]
```

### Port 8000 Already in Use
```bash
# Use different port
php -S localhost:9000

# Or find which process uses port 8000
netstat -ano | findstr :8000
```

### Login Not Working
1. Verify database has data: `SELECT * FROM enrollments LIMIT 1;`
2. Check session is enabled: `php -i | grep "Session Support"`
3. Clear browser cookies and try again

## Project Structure Overview

| File | Purpose |
|------|---------|
| `api.php` | RESTful API endpoint (all data operations) |
| `index.php` | Main HTML shell with UI panels |
| `script.js` | Frontend logic and API integration |
| `style.css` | Application styling |
| `config.php` | Database and app configuration |
| `db.php` | Database connection singleton |
| `database_schema.sql` | MySQL schema and seed data |
| `classes/` | Data access repositories |

## Useful Commands

### Check PHP Version
```bash
php -v
```

### Validate PHP Syntax
```bash
php -l api.php
php -l index.php
```

### Start Development Server (Verbose)
```bash
php -d error_reporting=E_ALL -S localhost:8000
```

### Test Database Connection
```bash
php test_connection.php
```

### View Database Logs
```bash
mysql -u root -p elearning_db -e "SELECT * FROM activity_logs LIMIT 10;"
```

## Common Tasks

### Add New Course
1. Log in as Admin
2. Use MySQL to insert: 
```sql
INSERT INTO projects (title, description, instructor, level, price) 
VALUES ('New Course', 'Description', 'Your Name', 'Beginner', 0);
```
3. Refresh browser

### Enroll Student Manually
```sql
INSERT INTO enrollments (project_id, student_name, email, status) 
VALUES (1, 'John Doe', 'john@example.com', 'Enrolled');
```

### Export Course Data
```bash
curl http://localhost:8000/api.php?action=courses > courses.json
```

## Performance Tips

### For Large Datasets
- Use `?limit=50&offset=0` parameters
- Implement pagination in UI
- Add database indexes for frequently searched columns

### For Better Experience
- Use modern browser (Chrome, Firefox, Edge)
- Enable JavaScript (required for app to work)
- Clear browser cache: Ctrl+Shift+Delete

## Next Steps

1. Read [API_DOCUMENTATION.md](API_DOCUMENTATION.md) for all endpoints
2. Check [PROJECT_STATUS.md](PROJECT_STATUS.md) for feature list
3. Review [DEPLOYMENT.md](DEPLOYMENT.md) for production setup
4. Explore database schema in [database_schema.sql](database_schema.sql)

## Mobile Testing

### Test Flutter App

```bash
# Navigate to Flutter app
cd flutter_app

# Get dependencies
flutter pub get

# Run on emulator/device
flutter run

# Update API URL in lib/services/api_service.dart
# Change: http://10.0.2.2:8000/ to your server URL
```

## Getting Help

### Check Logs
1. Browser console: F12 → Console tab
2. PHP errors: Check terminal running `php -S`
3. Database errors: Run test_connection.php

### Common Issues & Solutions

**Issue:** "No courses shown"
- Solution: Verify enrollments exist in database
- Check: `SELECT COUNT(*) FROM enrollments;`

**Issue:** "Assignment submission fails"
- Solution: Ensure you're enrolled in the course
- Check: Enroll first, then submit

**Issue:** "Grading modal not working"
- Solution: User must be instructor for course
- Check: Log in as instructor, select course you teach

## Support Resources

- **API Reference:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Deployment Guide:** [DEPLOYMENT.md](DEPLOYMENT.md)
- **Complete README:** [README.md](README.md)
- **Installation Guide:** [INSTALL.md](INSTALL.md)

---

Happy Learning! 🎓

For production deployment, follow the complete guide in [DEPLOYMENT.md](DEPLOYMENT.md).
