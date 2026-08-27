# Testing Guide - E-Learning Platform

## 🚀 Setup Prerequisites

Before testing, ensure you have completed:

1. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < database_schema.sql
   # When prompted for password, press Enter (or use your MySQL password)
   ```

2. **Upload Directories**
   ```bash
   # Create upload directories
   mkdir -p uploads/submissions
   mkdir -p uploads/resources
   mkdir -p uploads/avatars
   
   # Set permissions (on Windows, skip this)
   chmod -R 755 uploads/
   ```

3. **Configuration**
   - ✅ `.env` file created with default credentials
   - Update `.env` if you have different MySQL credentials
   - SMTP settings are optional (email won't send without them)

4. **Web Server Running**
   - Make sure Apache/PHP is running
   - Project accessible at `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/`

---

## PHPUnit Tests

Install the development dependencies and run the automated suite:

```bash
composer install
composer test
```

The suite covers authentication, logout/session state, role authorization, course validation, SQL-shaped input rejection, rate limiting, deadlines, and duplicate submissions. The duplicate-submission test uses SQLite when the PDO driver is available.

## ✅ Test 1: Login Page

### Step 1: Open Login Page
1. Go to: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/login.html`
2. You should see:
   - "Welcome back" heading
   - Email/Password input fields
   - Role selector buttons (Student, Instructor, Admin)
   - "Show demo credentials" button

### Step 2: View Demo Credentials
1. Click "Show demo credentials" button
2. You should see a box with three demo accounts:
   ```
   Student: student@learnflow.app / student123
   Instructor: instructor@learnflow.app / instructor123
   Admin: admin@learnflow.app / admin123
   ```

### Step 3: Test Login as Student
1. Select "Student" role button (should be highlighted)
2. Enter email: `student@learnflow.app`
3. Enter password: `student123`
4. Click "Sign in" button
5. **Expected Result:** Redirected to dashboard

### Step 4: Test Login as Instructor
1. Go back to login page
2. Select "Instructor" role button
3. Enter email: `instructor@learnflow.app`
4. Enter password: `instructor123`
5. Click "Sign in" button
6. **Expected Result:** Redirected to instructor dashboard

### Step 5: Test Login as Admin
1. Go back to login page
2. Select "Admin" role button
3. Enter email: `admin@learnflow.app`
4. Enter password: `admin123`
5. Click "Sign in" button
6. **Expected Result:** Redirected to admin dashboard

### Step 6: Test Invalid Login
1. Enter wrong email: `test@learnflow.app`
2. Enter wrong password: `wrongpass`
3. Click "Sign in" button
4. **Expected Result:** Error message displays

---

## ✅ Test 2: Registration Page

### Step 1: Open Registration Page
1. Go to: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/register.html`
2. You should see registration form with fields:
   - First Name
   - Last Name
   - Email
   - Password (8+ characters)
   - Password Confirmation
   - User Type dropdown
   - Terms & Conditions checkbox

### Step 2: Test Valid Registration
1. Fill in the form:
   - First Name: `John`
   - Last Name: `Doe`
   - Email: `john.doe@learnflow.app`
   - Password: `SecurePass123`
   - Password Confirmation: `SecurePass123`
   - Select User Type: `Student`
   - Check Terms checkbox
2. Click "Create account" button
3. **Expected Result:** 
   - Success message
   - Redirects to login page
   - New user can login

### Step 3: Test Password Mismatch
1. Fill form with:
   - Password: `SecurePass123`
   - Password Confirmation: `DifferentPass456`
2. Click "Create account"
3. **Expected Result:** Error message "Passwords do not match"

### Step 4: Test Short Password
1. Fill form with password: `short`
2. Click "Create account"
3. **Expected Result:** Error message "Password must be at least 8 characters"

### Step 5: Test Duplicate Email
1. Try to register with: `student@learnflow.app` (demo account)
2. Click "Create account"
3. **Expected Result:** Error message "Email already exists"

---

## ✅ Test 3: Course Detail Page

### Step 1: Login as Student
1. Complete login test (Step 3 from Test 1)
2. You should be on the dashboard

### Step 2: Navigate to Courses
1. Look for course link or navigation
2. Open course detail: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/course-detail.html?id=101`
3. **Expected Result:** Course details page loads with:
   - Course title
   - Description
   - Price (if paid)
   - Enrollment button
   - Course tabs (Overview, Lessons, Instructor)

### Step 3: View Lessons
1. Click on "Lessons" tab
2. **Expected Result:** List of course lessons displays

### Step 4: View Instructor
1. Click on "Instructor" tab
2. **Expected Result:** Instructor profile information displays

---

## ✅ Test 4: Assignment Submission

### Step 1: Login as Student
1. Use student credentials to login

### Step 2: Open Assignment Submission
1. Go to: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/submit-assignment.html?id=1`
2. You should see:
   - Assignment title and description
   - Submission type selector (Text only, File only, Both)
   - File upload zone
   - Text area for written submission

### Step 3: Submit Text-Only
1. Select "Text only" option
2. Enter some text in the textarea
3. Click "Submit Assignment"
4. **Expected Result:** Success message, submission saved

### Step 4: Submit File-Only
1. Select "File only" option
2. Upload a PDF or text file
3. Click "Submit Assignment"
4. **Expected Result:** File uploaded to /uploads/submissions/

### Step 5: Submit Both
1. Select "Both" option
2. Enter text and upload a file
3. Click "Submit Assignment"
4. **Expected Result:** Both text and file saved

---

## ✅ Test 5: Instructor Grading

### Step 1: Login as Instructor
1. Use instructor credentials to login

### Step 2: Open Grading Page
1. Go to: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/instructor-grading.html`
2. You should see:
   - Course filter dropdown
   - Assignment filter dropdown
   - Status filter
   - Submissions list

### Step 3: Filter Submissions
1. Select a course from dropdown
2. Select an assignment
3. Filter by status (Pending, Graded)
4. **Expected Result:** Submissions list updates

### Step 4: Grade a Submission
1. Click on a submission from the list
2. Enter score (0-100): `85`
3. Enter feedback: `Good work, well explained`
4. Click "Submit Grade"
5. **Expected Result:** 
   - Submission marked as graded
   - Grade and feedback saved

---

## ✅ Test 6: Admin User Management

### Step 1: Login as Admin
1. Use admin credentials to login

### Step 2: Open User Management
1. Go to: `http://localhost/E-learning-Project-Management-System--main/E-learning-Project-Management-System--main/admin-users.html`
2. You should see:
   - User search bar
   - Role filter dropdown
   - Paginated user list with columns:
     * Name
     * Email
     * Role
     * Joined date
     * Actions (Edit, Delete)

### Step 3: Search Users
1. Type `student` in search bar
2. **Expected Result:** List filters to show matching users

### Step 4: Filter by Role
1. Select "Instructor" from role filter
2. **Expected Result:** List shows only instructors

### Step 5: Add New User
1. Click "+ Add User" button
2. Fill form:
   - First Name: `Jane`
   - Last Name: `Smith`
   - Email: `jane.smith@learnflow.app`
   - Password: `NewPassword123`
   - Role: `Instructor`
3. Click "Create User"
4. **Expected Result:** 
   - User added to list
   - Confirmation message

### Step 6: Delete User
1. Find a user in the list
2. Click "Delete" button
3. Confirm deletion
4. **Expected Result:** User soft-deleted (still in DB but marked deleted)

---

## 🔍 Browser Console Debugging

### Check Network Requests
1. Open browser DevTools (F12)
2. Go to Network tab
3. Perform login
4. You should see requests to:
   - `api.php?action=login` (POST)
   - Response should show success status

### Check Console Errors
1. Open DevTools Console (F12 > Console)
2. You should see no red error messages
3. Login should show success message

### Check Application Storage
1. Open DevTools (F12)
2. Go to Application > Cookies
3. After login, you should see session cookie

---

## 📊 Database Verification

### Verify Users Table
```sql
SELECT * FROM users WHERE deleted_at IS NULL;
```
Should show demo users and any created test users.

### Verify Submissions
```sql
SELECT s.id, s.student_email, s.file_url, s.submitted_at 
FROM submissions s
WHERE s.deleted_at IS NULL
ORDER BY s.submitted_at DESC;
```
Should show submitted assignments.

### Verify Grades
```sql
SELECT s.id, s.score, s.feedback, s.graded_at 
FROM submissions s
WHERE s.score IS NOT NULL;
```
Should show graded submissions.

---

## ⚙️ Configuration Testing

### Test Database Connection
```php
// Create test.php in root directory
<?php
require_once __DIR__ . '/db.php';
try {
    $pdo = getDatabase();
    echo "✅ Database connected successfully!";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
```
Visit: `http://localhost/.../test.php`

### Test File Upload
1. Submit assignment with file
2. Check `/uploads/submissions/` directory
3. File should be there with format: `assignment_{id}_student_{id}_{timestamp}.ext`

### Test Email (Optional)
If SMTP is configured:
1. Register new user
2. Check email for welcome message
3. Verify email template rendering

---

## 🐛 Troubleshooting

### "Database connection failed"
- Check DB_HOST, DB_USER, DB_PASSWORD in `.env`
- Verify MySQL is running
- Verify database name in `.env` matches created database

### "File upload failed"
- Check `/uploads/` directory exists
- Verify directory permissions (755)
- Check MAX_UPLOAD_SIZE in .env
- File size should be < 50MB

### "Login redirects to login page again"
- Check browser cookies enabled
- Check Session settings in `.env`
- Verify api.php has correct response format

### "404 errors on pages"
- Check file paths are correct
- Verify all .html files exist in root directory
- Check .htaccess rewrite rules

### "Submission doesn't save"
- Check assignments table has records
- Verify submission POST request succeeds (check Network tab)
- Check file permissions on /uploads/

---

## 📝 Test Results Checklist

- [ ] Login page loads correctly
- [ ] Student login works
- [ ] Instructor login works
- [ ] Admin login works
- [ ] Invalid credentials show error
- [ ] Registration creates new user
- [ ] Password validation works
- [ ] Course detail page loads
- [ ] Course lessons display
- [ ] Assignment submission saves
- [ ] File upload works
- [ ] Instructor grading interface works
- [ ] Grade submission saves
- [ ] Admin user list displays
- [ ] User search filters correctly
- [ ] Add user creates new account
- [ ] Delete user soft-deletes
- [ ] No JavaScript errors in console
- [ ] No PHP errors in error_log

---

## 🎓 Next Steps After Testing

1. **If All Tests Pass:**
   - ✅ System is working correctly
   - Proceed with advanced features
   - Set up real SMTP for email
   - Configure Stripe/PayPal

2. **If Tests Fail:**
   - Check error messages in console/error_log
   - Verify database tables created
   - Check file permissions
   - Review .env configuration
   - See troubleshooting section

3. **Ready for Production:**
   - Set up domain and HTTPS
   - Configure real email service
   - Add Stripe/PayPal SDKs
   - Set up automated backups
   - Enable rate limiting

---

**Good luck with testing! If you encounter any issues, check the browser console (F12) for error messages.**
