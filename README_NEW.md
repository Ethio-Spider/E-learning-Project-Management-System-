# E-Learning Management System v2.0

A modern, complete e-learning platform for managing courses, resources, and student enrollments. Built with PHP 8+, MySQL, and modern JavaScript.

## 🎯 Features

### Course Management
- ✅ Browse and search courses by title, description, category, and instructor
- ✅ Filter courses by difficulty level (Beginner, Intermediate, Advanced)
- ✅ Create, edit, and delete courses
- ✅ Add course images, descriptions, and detailed information
- ✅ Soft delete functionality with restore capability
- ✅ Course status management (Active, Draft, Archived)

### Enrollment Management
- ✅ Student enrollment in courses
- ✅ Automatic duplicate prevention
- ✅ Track enrollment status (Enrolled, Completed, Cancelled)
- ✅ Enrollment statistics and analytics
- ✅ Progress tracking
- ✅ Enrollment history

### Resource Management
- ✅ Manage course resources (videos, documents, links, assignments, quizzes)
- ✅ Organize resources with custom ordering
- ✅ Track resource types and mark required resources
- ✅ Resource descriptions and metadata

### Technical Features
- ✅ RESTful JSON API
- ✅ Comprehensive logging system
- ✅ Input validation and sanitization
- ✅ Activity audit trails
- ✅ Error handling and recovery
- ✅ CORS support
- ✅ Fully responsive design
- ✅ WCAG accessibility compliance

## 📋 Requirements

- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher (8.0 recommended)
- **Web Server**: Apache, Nginx, or similar
- **Browser**: Modern browser with ES6+ JavaScript support

## 🚀 Installation

### 1. Clone/Download the Project
```bash
cd your-projects-folder
# Extract the files or clone the repository
```

### 2. Create Database
```bash
mysql -u root -p < database_schema.sql
```

### 3. Configure Environment
Copy the example environment file and update with your settings:
```bash
cp .env.example .env
```

Edit `.env` with your database credentials:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=elearning_db
DB_USER=root
DB_PASSWORD=your_password
DB_TIMEZONE=UTC
```

### 4. Set Permissions
Ensure the logs and uploads directories are writable:
```bash
mkdir -p logs uploads
chmod 755 logs uploads
```

### 5. Start Development Server
```bash
# Using PHP built-in server
php -S localhost:8000

# Then open: http://localhost:8000
```

## 📁 Project Structure

```
.
├── api.php                      # Main API endpoints
├── index.php                    # Frontend HTML
├── db.php                       # Database connection
├── config.php                   # Configuration management
├── script.js                    # Frontend JavaScript
├── style.css                    # Frontend styles
├── database_schema.sql          # Database schema
├── .env.example                 # Environment example
├── .htaccess                    # Apache rewrite rules
├── classes/                     # PHP classes
│   ├── Logger.php              # Logging system
│   ├── Response.php            # API response handling
│   ├── Validator.php           # Input validation
│   ├── CourseRepository.php    # Course data operations
│   ├── EnrollmentRepository.php # Enrollment operations
│   └── ResourceRepository.php  # Resource operations
├── logs/                        # Application logs
├── uploads/                     # User uploads
└── README.md                    # This file
```

## 🔌 API Endpoints

### Courses

**Get all courses**
```
GET /api.php
GET /api.php?category=Web%20Development&level=Beginner
```

**Get course details**
```
GET /api.php?action=get&id=1
```

**Search courses**
```
GET /api.php?action=search&q=php
```

**Create course**
```
POST /api.php?action=create
Content-Type: application/json

{
  "title": "Course Title",
  "description": "Course description",
  "category": "Web Development",
  "instructor": "John Doe",
  "duration": "8 weeks",
  "level": "Beginner",
  "status": "Active",
  "image_url": "https://example.com/image.jpg"
}
```

**Update course**
```
PUT /api.php?action=update&id=1
Content-Type: application/json

{
  "title": "Updated Title",
  "description": "Updated description",
  ...
}
```

**Delete course**
```
DELETE /api.php?action=delete&id=1
```

### Enrollments

**Get course enrollments**
```
GET /api.php?action=enrollments&project_id=1
```

**Enroll student**
```
POST /api.php?action=enroll
Content-Type: application/json

{
  "project_id": 1,
  "student_name": "John Smith",
  "email": "john@example.com"
}
```

**Update enrollment**
```
PUT /api.php?action=update-enrollment&id=1
Content-Type: application/json

{
  "status": "Completed",
  "progress": 100
}
```

**Get enrollment statistics**
```
GET /api.php?action=enrollment-stats&project_id=1
```

### Resources

**Get course resources**
```
GET /api.php?action=resources&project_id=1
```

**Create resource**
```
POST /api.php?action=create-resource
Content-Type: application/json

{
  "project_id": 1,
  "title": "Resource Title",
  "type": "Video",
  "file_url": "https://example.com/video.mp4",
  "description": "Resource description"
}
```

## 🔐 Security Features

- Input validation and sanitization
- SQL injection prevention (prepared statements)
- XSS protection (HTML escaping)
- CORS headers
- Soft deletes (data recovery)
- Activity logging
- Error handling without exposing sensitive info

## 📊 Database Schema

### Tables

**projects** - Courses
- id, title, description, category, instructor, duration
- level, status, image_url, price, rating
- created_at, updated_at, deleted_at

**enrollments** - Student enrollments
- id, project_id, student_name, email
- enrollment_date, status, progress, completed_at

**resources** - Course resources
- id, project_id, title, type, file_url
- description, position, is_required

**activity_logs** - Audit trail
- id, project_id, action, details, created_at

**api_logs** - API request logs
- id, method, endpoint, status_code, response_time

## 🎨 Customization

### Change Colors
Edit CSS variables in `style.css`:
```css
:root {
    --primary: #2563eb;
    --success: #16a34a;
    --danger: #dc2626;
    ...
}
```

### Add Custom Validation
Edit validation methods in `classes/Validator.php`:
```php
public static function validateCourse(array $data): array
{
    // Add your custom validation logic
}
```

### Extend API
Add new handler functions in `api.php`:
```php
case $method === 'GET' && $action === 'custom-action':
    handleCustomAction($pdo, $logger);
```

## 🧪 Testing

### Manual Testing Checklist
- [ ] Create a course
- [ ] Edit course details
- [ ] Delete a course
- [ ] Search courses by query
- [ ] Filter by category and level
- [ ] Enroll a student
- [ ] Check enrollment statistics
- [ ] Verify responsive design on mobile
- [ ] Test all keyboard navigation

### API Testing with cURL
```bash
# Get all courses
curl http://localhost:8000/api.php

# Create course
curl -X POST http://localhost:8000/api.php?action=create \
  -H "Content-Type: application/json" \
  -d '{"title":"New Course","description":"Description","category":"Web","instructor":"John","duration":"8 weeks","level":"Beginner","status":"Active"}'

# Search
curl "http://localhost:8000/api.php?action=search&q=php"
```

## 📝 Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| DB_HOST | 127.0.0.1 | Database host |
| DB_PORT | 3306 | Database port |
| DB_NAME | elearning_db | Database name |
| DB_USER | root | Database user |
| DB_PASSWORD | (empty) | Database password |
| APP_DEBUG | false | Debug mode |
| LOG_LEVEL | INFO | Logging level |
| RATE_LIMIT | 100 | API rate limit |

## 📚 Documentation

- API responses include `success`, `message`, and optional `data` fields
- All timestamps are in UTC format
- Pagination uses `limit` and `offset` parameters
- Validation errors return with status code 422

## 🐛 Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify database credentials in `.env`
- Ensure database exists: `CREATE DATABASE elearning_db;`

### File Upload Issues
- Check `logs/` and `uploads/` directories are writable
- Run: `chmod 755 logs uploads`

### API Returns 404
- Verify Apache mod_rewrite is enabled
- Check `.htaccess` file exists and is readable

### No Logs Appearing
- Check `logs/` directory exists
- Verify write permissions: `chmod 755 logs`
- Check `LOG_LEVEL` in `.env`

## 🤝 Contributing

To extend this project:
1. Add new repository classes for new entities
2. Create new API handlers
3. Add corresponding frontend functions
4. Update the database schema if needed
5. Test thoroughly

## 📄 License

This project is provided as-is for educational purposes.

## 🎓 Learning Points

This project demonstrates:
- Modern PHP practices (type declarations, strict types)
- Repository pattern for data access
- RESTful API design
- Responsive web design
- Frontend-backend integration
- Database design with relationships
- Input validation and sanitization
- Error handling and logging
- Accessibility best practices

## 📞 Support

For issues or questions, refer to the comments in the source code or check the troubleshooting section above.

---

**Version**: 2.0.0  
**Last Updated**: August 2026  
**Status**: Production Ready
