# 🎯 E-Learning Platform - Quick Reference Card

## 📌 Demo Credentials

### Login Credentials
```
┌─────────────────────────────────────────────────────┐
│ STUDENT                                             │
│ Email: student@learnflow.app                        │
│ Password: student123                                │
│ Role: Student                                       │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ INSTRUCTOR                                          │
│ Email: instructor@learnflow.app                     │
│ Password: instructor123                             │
│ Role: Instructor                                    │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ ADMIN                                               │
│ Email: admin@learnflow.app                          │
│ Password: admin123                                  │
│ Role: Admin                                         │
└─────────────────────────────────────────────────────┘
```

---

## 🔗 Quick Links

| Feature | URL | Notes |
|---------|-----|-------|
| **Verify Setup** | `verify-setup.php` | Run this first to check installation |
| **Login** | `login.html` | Authentication page |
| **Register** | `register.html` | Create new account |
| **Dashboard** | `index.html` | Main dashboard (after login) |
| **Courses** | `course-detail.html?id=101` | View course details |
| **Submit Assignment** | `submit-assignment.html?id=1` | Submit coursework |
| **Grade Work** | `instructor-grading.html` | Instructor grading panel |
| **Users Management** | `admin-users.html` | Admin user management |

---

## ✅ First Time Setup

### Step 1: Verify Installation
```
1. Open browser
2. Go to: http://localhost/.../verify-setup.php
3. Check that all items show ✓ (green checkmarks)
```

### Step 2: Configure Database
```
1. Copy .env.example to .env
2. Update database credentials if needed:
   - DB_HOST=localhost
   - DB_USER=root
   - DB_PASSWORD=(your password)
3. Import schema: mysql -u root -p < database_schema.sql
```

### Step 3: Test Login
```
1. Open: http://localhost/.../login.html
2. Select "Student" role
3. Enter: student@learnflow.app / student123
4. Click "Sign in"
```

---

## 📋 Testing Checklist

### Authentication
- [ ] Login with student credentials ✓
- [ ] Login with instructor credentials ✓
- [ ] Login with admin credentials ✓
- [ ] Invalid credentials show error ✓
- [ ] Registration creates new user ✓

### Core Features
- [ ] View course details ✓
- [ ] Submit text assignment ✓
- [ ] Upload file assignment ✓
- [ ] Grade submission (as instructor) ✓
- [ ] Manage users (as admin) ✓

### Admin Functions
- [ ] Search users ✓
- [ ] Filter by role ✓
- [ ] Add new user ✓
- [ ] Delete user ✓

---

## 🔧 Database Commands

### Import Schema
```bash
mysql -u root -p < database_schema.sql
```

### View Demo Users
```sql
SELECT * FROM users WHERE role IN ('student', 'instructor', 'admin');
```

### View Submissions
```sql
SELECT s.id, s.student_email, s.submitted_at, s.score 
FROM submissions s 
ORDER BY s.submitted_at DESC;
```

### Clear Test Data (Carefully!)
```sql
DELETE FROM submissions WHERE id > 0;
DELETE FROM users WHERE email LIKE '%.app%' AND role = 'student';
```

---

## 📁 Project Structure

```
E-learning-Project-Management-System/
├── 📄 login.html                 # Login page
├── 📄 register.html              # Registration
├── 📄 course-detail.html         # Course viewing
├── 📄 submit-assignment.html     # Assignment submission
├── 📄 instructor-grading.html    # Grading interface
├── 📄 admin-users.html           # User management
├── 📄 index.php                  # Dashboard
├── 📄 api.php                    # API endpoints
│
├── 📂 classes/
│   ├── UserRepository.php
│   ├── CourseRepository.php
│   ├── AssignmentRepository.php
│   ├── FileUploadHandler.php
│   ├── NotificationService.php
│   ├── PaymentService.php
│   └── ...
│
├── 📂 uploads/
│   ├── submissions/              # Assignment files
│   ├── resources/                # Course materials
│   └── avatars/                  # User profile pics
│
├── 📄 .env                       # Configuration (create from .env.example)
├── 📄 config.php                 # App config
├── 📄 db.php                     # Database connection
└── 📄 database_schema.sql        # Database tables
```

---

## 🐛 Troubleshooting Quick Guide

### Issue: "Database connection failed"
**Solution:** Check `.env` file
```bash
# Verify in .env:
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=elearning_db
```

### Issue: "File upload failed"
**Solution:** Create upload directories
```bash
mkdir -p uploads/submissions
mkdir -p uploads/resources
mkdir -p uploads/avatars
```

### Issue: "Login redirects to login page"
**Solution:** Check browser cookies
- Open DevTools (F12)
- Go to Application > Cookies
- Verify session cookie exists after login

### Issue: "Page not found (404)"
**Solution:** Verify file paths
- Check files exist in project root
- Use `verify-setup.php` to confirm all files

### Issue: "White page or PHP error"
**Solution:** Check PHP error log
```bash
# Look for errors in:
# Windows: C:\xampp\apache\logs\error.log
# Linux: /var/log/apache2/error.log
# macOS: /var/log/apache2/error_log
```

---

## 🚀 Common Tasks

### Create Test User
1. Go to: `register.html`
2. Fill form with new email
3. Password: `TestPass123`
4. Submit

### Grade an Assignment
1. Login as instructor
2. Go to: `instructor-grading.html`
3. Select course & assignment
4. Click submission
5. Enter score (0-100)
6. Add feedback
7. Click "Submit Grade"

### Add User as Admin
1. Login as admin
2. Go to: `admin-users.html`
3. Click "+ Add User"
4. Fill form
5. Click "Create User"

### Test File Upload
1. Go to: `submit-assignment.html?id=1`
2. Select "File only" option
3. Drag & drop or click to upload
4. Click "Submit Assignment"
5. Check: `/uploads/submissions/` folder

---

## 📊 API Endpoints Reference

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/api.php?action=login` | User authentication |
| POST | `/api.php?action=register` | New user registration |
| GET | `/api.php?action=me` | Get current user |
| GET | `/api.php?action=admin-users` | List all users (admin) |
| POST | `/api.php?action=delete-user` | Delete user (admin) |
| GET | `/api.php?action=course` | Get course details |
| POST | `/api.php?action=submit-assignment` | Submit assignment |
| GET | `/api.php?action=grading-submissions` | List submissions (instructor) |
| POST | `/api.php?action=grade-submission` | Grade submission (instructor) |

---

## 💡 Pro Tips

✨ **Tip 1:** Use demo credentials for quick testing
- No need to register, just use student@learnflow.app / student123

✨ **Tip 2:** Open DevTools (F12) to debug
- Check Network tab for API calls
- Check Console for JavaScript errors

✨ **Tip 3:** View HTTP responses
- Network tab shows API response JSON
- Look for "success": true/false

✨ **Tip 4:** Check file uploads
- Files go to `/uploads/submissions/`
- Names are like: `assignment_1_student_1_1692892341.pdf`

✨ **Tip 5:** Database queries
- Use MySQL client to verify data
- Check both submission status and grades

---

## 📞 Need Help?

1. **Setup Issues?** → Check `TESTING_GUIDE.md`
2. **API Questions?** → See `API_DOCUMENTATION.md`
3. **Technical Details?** → Read `BUILD_SUMMARY.md`
4. **Getting Started?** → Review `GETTING_STARTED.md`
5. **Code Questions?** → Check inline comments in source files

---

## ✅ Ready to Go!

```
✓ .env file configured
✓ Database imported
✓ Upload directories created
✓ verify-setup.php passed all checks
✓ Login credentials ready

👉 Start testing at: http://localhost/.../login.html
```

---

**Last Updated:** 2026-08-16  
**Platform Version:** 1.0  
**Status:** Ready for Testing ✅
