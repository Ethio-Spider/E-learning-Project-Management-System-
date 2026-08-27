# LearnFlow Pro - E-Learning Platform

A complete, modern e-learning platform built with PHP, MySQL, and Flutter. Features student dashboards, course management, assignment submission, instructor grading, and progress analytics.

## Features

### 🎓 For Students
- Multi-role dashboard (Student, Instructor, Admin)
- Browse and enroll in courses
- Submit assignments and get feedback
- Track progress and analytics
- View certificates
- Discussion forum access
- AI learning assistant

### 👨‍🏫 For Instructors
- Manage courses and create assignments
- View student enrollments and progress
- Grade submissions with feedback
- Track course analytics
- Monitor completion rates

### 🛠️ For Admins
- Platform-wide analytics
- User management
- Course administration
- Financial reports (optional payment system)

### 📱 Mobile
- Flutter app with course browsing and enrollment
- Assignment submission on mobile
- Progress tracking
- Push notifications (ready to integrate)

### Flutter application architecture

The mobile client is a real API client, not a prototype mock:

- `ApiService` talks to `api.php`, carries the PHP session cookie, and refreshes CSRF tokens.
- `flutter_secure_storage` persists the session cookie and CSRF token between launches.
- `ApiException` turns network, timeout, malformed-response, and HTTP failures into user-facing error states.
- `AuthGate` restores the session on startup and provides a retry path when the backend is unavailable.
- Course browsing loads live data with loading, empty, error, pull-to-refresh, and adaptive grid states.

Run the app against a local backend:

```bash
cd flutter_app
flutter pub get
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api.php
```

Use `http://127.0.0.1:8000/api.php` for a desktop or iOS simulator, and replace the host with the machine's LAN IP for a physical device. Production deployments should use HTTPS.

## Project Structure

```
E-learning-Project-Management-System-main/
├── api.php                          # RESTful API endpoints
├── index.php                        # Main application shell
├── script.js                        # Frontend interactivity
├── style.css                        # Application styling
├── config.php                       # Configuration management
├── db.php                          # Database connection
├── database_schema.sql             # Database schema
├── classes/
│   ├── CourseRepository.php        # Course CRUD operations
│   ├── EnrollmentRepository.php    # Enrollment management
│   ├── AssignmentRepository.php    # Assignment/submission handling
│   ├── ProgressRepository.php      # Progress analytics
│   ├── Validator.php               # Input validation
│   ├── Logger.php                  # Activity logging
│   └── Response.php                # API response formatting
└── flutter_app/                    # Flutter mobile application
    ├── lib/
    │   ├── main.dart
    │   ├── screens/
    │   └── services/
    └── pubspec.yaml
```

## Tech Stack

- **Backend**: PHP 8+, MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Mobile**: Flutter 3.0+
- **Database**: MySQL with PDO
- **API**: RESTful JSON

## Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer (optional, for dependency management)
- Flutter SDK 3.0+ (for mobile app)

## Installation

### 1. Clone or Download

```bash
git clone https://github.com/yourusername/e-learning-platform.git
cd E-learning-Project-Management-System-main
```

### 2. Database Setup

Create a MySQL database and import the schema:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE elearning_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE elearning_db;
SOURCE database_schema.sql;
```

Or use the command line:

```bash
mysql -u root -p elearning_db < database_schema.sql
```

### 3. Configure Database

Edit `config.php` with your database credentials:

```php
return [
    'DATABASE' => [
        'host' => 'localhost',      // Your MySQL host
        'port' => 3306,             // MySQL port
        'name' => 'elearning_db',   // Database name
        'user' => 'root',           // MySQL user
        'password' => '',           // MySQL password
        'charset' => 'utf8mb4',
        'timezone' => '+00:00',
    ],
    'APP' => [
        'debug' => true,
        'url' => 'http://localhost',
    ]
];
```

### 4. Run Locally

Using PHP's built-in server:

```bash
php -S localhost:8000
```

Access at: `http://localhost:8000`

## Demo Credentials

| Role | Email | Password |
|------|-------|----------|
| Student | student@learnflow.app | student123 |
| Instructor | instructor@learnflow.app | instructor123 |
| Admin | admin@learnflow.app | admin123 |

## API Endpoints

### Authentication
- `POST /api.php?action=login` - User login
- `GET /api.php?action=me` - Get current user
- `POST /api.php?action=logout` - User logout

### Courses
- `GET /api.php?action=courses` - List all courses
- `GET /api.php?action=course&id=X` - Get course details
- `POST /api.php?action=enroll` - Enroll in course
- `GET /api.php?action=my-courses` - Get user's enrollments

### Assignments
- `GET /api.php?action=assignments&course_id=X` - Get course assignments
- `GET /api.php?action=assignment&id=X` - Get assignment details
- `POST /api.php?action=submit-assignment` - Submit assignment
- `GET /api.php?action=submissions&assignment_id=X` - View submissions (instructor)
- `POST /api.php?action=grade-submission` - Grade submission (instructor)

### Analytics
- `GET /api.php?action=course-analytics&course_id=X` - Course analytics
- `GET /api.php?action=instructor-courses` - Instructor's courses
- `GET /api.php?action=course-stats` - Platform statistics

### AI Assistance
- `POST /api.php?action=ai-chat` - Get AI guidance

## Features in Detail

### Course Management
- Create, edit, and archive courses
- Set difficulty levels and duration
- Manage course resources and materials
- Track student enrollments

### Assignment Workflow
1. Instructor creates assignment with due date
2. Students submit work before deadline
3. Instructor grades with score and feedback
4. Students view grades and feedback

### Progress Tracking
- Per-course completion percentage
- Average assignment scores
- Submission statistics
- Learning analytics dashboard

### Multi-Role Access Control
- Students: See only their own courses, assignments, grades
- Instructors: Manage assigned courses, grade submissions, view class analytics
- Admins: Platform-wide management and reporting

## Security Features

- Session-based authentication
- Password validation
- SQL injection prevention (PDO prepared statements)
- CSRF protection via token validation
- Input sanitization
- Role-based access control

## Database Schema Highlights

### Tables
- **projects** - Courses/learning paths
- **resources** - Course materials (videos, docs, links)
- **enrollments** - Student course registrations
- **assignments** - Course assignments
- **submissions** - Assignment submissions with grades
- **activity_logs** - User activity tracking
- **api_logs** - API request logging

### Indexes
- Full-text search on courses
- Performance indexes on foreign keys
- Soft deletes with deleted_at timestamps

## Deployment

### Production Checklist

1. **Update config.php**
   ```php
   'debug' => false,  // Disable debug mode
   ```

2. **Enable HTTPS**
   - Get SSL certificate
   - Update config.php with production URL

3. **Database Backup**
   ```bash
   mysqldump -u user -p database_name > backup.sql
   ```

4. **Secure Credentials**
   - Use environment variables
   - Never commit passwords to version control
   - Use strong, unique database password

5. **Set File Permissions**
   ```bash
   chmod 755 .
   chmod 644 *.php *.js *.css
   ```

### Hosting Options

- **Shared Hosting**: GoDaddy, Bluehost, HostGator (PHP + MySQL support)
- **VPS**: Linode, DigitalOcean, AWS Lightsail
- **Docker**: Containerize with PHP-FPM + MySQL
- **Cloud**: AWS, Azure, Google Cloud (managed databases)

### Docker Deployment (Optional)

```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html
```

## Flutter Mobile App

### Setup

```bash
cd flutter_app
flutter pub get
flutter run
```

### Key Features
- Authentication screen
- Course browsing
- Assignment submission
- Dashboard views
- Notification handling

## Performance Optimization

- Database query optimization with indexes
- Lazy loading for course resources
- API response caching
- CSS/JS minification recommended
- Image optimization for mobile

## Testing

### Manual Testing
1. Create test accounts
2. Enroll in courses
3. Submit assignments
4. Grade as instructor
5. Verify progress tracking

### API Testing
Use tools like Postman or curl:

```bash
# Login
curl -X POST http://localhost:8000/api.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@learnflow.app","password":"student123"}'

# Get courses
curl http://localhost:8000/api.php?action=courses
```

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check credentials in config.php
- Ensure database exists

### Login Issues
- Clear browser cookies
- Check session folder permissions (temp/)
- Verify credentials are correct

### Assignment Submission Fails
- Check database permissions
- Verify POST request is valid JSON
- Check max file upload size

## Future Enhancements

- [ ] Payment integration (Stripe, PayPal)
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] Advanced analytics dashboards
- [ ] Video streaming optimization
- [ ] Real-time chat/messaging
- [ ] Learning paths and prerequisites
- [ ] Peer review system
- [ ] Gamification (badges, leaderboards)

## Support & Documentation

- Check database_schema.sql for table structure
- Review api.php for endpoint documentation
- See classes/ for business logic details
- JavaScript in script.js for UI interactions

## License

This project is provided as-is for educational purposes.

## Contributors

Built as a comprehensive e-learning platform with PHP, MySQL, and Flutter.

---

**Last Updated**: 2026-08-16  
**Platform Version**: 1.0.0
