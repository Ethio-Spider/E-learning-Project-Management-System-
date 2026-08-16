# API Documentation - LearnFlow Pro

## Base URL

```
http://localhost:8000/api.php
```

## Authentication

All endpoints except login require active session authentication.

### Login
```
POST /api.php?action=login
Content-Type: application/json

{
  "email": "student@learnflow.app",
  "password": "student123"
}

Response:
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "role": "student",
    "user": {
      "name": "Student User",
      "email": "student@learnflow.app",
      "role": "student"
    },
    "dashboard": { ... }
  }
}
```

### Get Current User
```
GET /api.php?action=me

Response:
{
  "success": true,
  "data": {
    "user": {
      "name": "Student User",
      "email": "student@learnflow.app",
      "role": "student"
    },
    "role": "student"
  }
}
```

### Logout
```
POST /api.php?action=logout

Response:
{
  "success": true,
  "message": "Logged out successfully."
}
```

## Course Endpoints

### List All Courses
```
GET /api.php?action=courses
Query Parameters:
  - category: string (optional)
  - level: string (optional) - Beginner, Intermediate, Advanced
  - search: string (optional)
  - limit: integer (default: 20)
  - offset: integer (default: 0)

Response:
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Web Development",
        "description": "Learn HTML, CSS, JavaScript...",
        "category": "Web Development",
        "instructor": "John Doe",
        "level": "Beginner",
        "duration": "8 weeks",
        "price": 0,
        "rating": 4.8,
        "total_ratings": 245
      }
    ]
  }
}
```

### Get Course Details
```
GET /api.php?action=course&id=1

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Introduction to Web Development",
    ...course details...
  }
}
```

### Enroll in Course
```
POST /api.php?action=enroll
Content-Type: application/json

{
  "course_id": 1
}

Response:
{
  "success": true,
  "message": "Enrollment successful.",
  "data": {
    "enrollment_id": 5
  }
}
```

### Get User's Courses
```
GET /api.php?action=my-courses

Response:
{
  "success": true,
  "data": {
    "enrollments": [
      {
        "id": 5,
        "project_id": 1,
        "student_name": "Student User",
        "email": "student@learnflow.app",
        "status": "Enrolled",
        "progress": 45.5
      }
    ]
  }
}
```

## Assignment Endpoints

### Get Course Assignments
```
GET /api.php?action=assignments&course_id=1

Response:
{
  "success": true,
  "data": {
    "assignments": [
      {
        "id": 1,
        "title": "Build Your First Webpage",
        "description": "Create a personal portfolio...",
        "due_date": "2026-08-23",
        "max_score": 100,
        "status": "Open"
      }
    ]
  }
}
```

### Get Assignment Details
```
GET /api.php?action=assignment&id=1

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Build Your First Webpage",
    ...assignment details...,
    "submission": {  // Only for enrolled students
      "id": 10,
      "submission_text": "...",
      "submitted_at": "2026-08-20 14:30:00",
      "score": 85,
      "feedback": "Great work!"
    }
  }
}
```

### Submit Assignment
```
POST /api.php?action=submit-assignment
Content-Type: application/json

{
  "assignment_id": 1,
  "submission_text": "My response to the assignment..."
}

Response:
{
  "success": true,
  "message": "Assignment submitted successfully.",
  "data": {
    "submission_id": 10
  }
}
```

### Get Assignment Submissions (Instructor)
```
GET /api.php?action=submissions&assignment_id=1
Authorization: Required (Instructor/Admin only)

Response:
{
  "success": true,
  "data": {
    "submissions": [
      {
        "id": 10,
        "student_email": "student@learnflow.app",
        "student_name": "Student User",
        "submission_text": "...",
        "submitted_at": "2026-08-20 14:30:00",
        "score": null,
        "feedback": null,
        "graded_at": null
      }
    ]
  }
}
```

### Grade Submission (Instructor)
```
POST /api.php?action=grade-submission
Authorization: Required (Instructor/Admin only)
Content-Type: application/json

{
  "submission_id": 10,
  "score": 85,
  "feedback": "Great work! You demonstrated strong understanding..."
}

Response:
{
  "success": true,
  "message": "Submission graded successfully."
}
```

## Instructor Endpoints

### Get Instructor's Courses
```
GET /api.php?action=instructor-courses
Authorization: Required (Instructor/Admin only)

Response:
{
  "success": true,
  "data": {
    "courses": [
      {
        "id": 1,
        "title": "Introduction to Web Development",
        ...course details...
      }
    ]
  }
}
```

### Get Course Analytics
```
GET /api.php?action=course-analytics&course_id=1
Authorization: Required (Instructor/Admin only)

Response:
{
  "success": true,
  "data": {
    "analytics": {
      "total_students": 45,
      "active_count": 40,
      "completed_count": 5,
      "avg_completion": 62.3,
      "total_submissions": 120,
      "graded_submissions": 95
    },
    "enrollments": [
      {
        "id": 5,
        "student_name": "Student User",
        "email": "student@learnflow.app",
        "progress": 45.5,
        "avg_score": 82.3,
        "submitted_count": 4,
        "graded_count": 3
      }
    ]
  }
}
```

### Get Platform Statistics
```
GET /api.php?action=course-stats
Authorization: Required (Instructor/Admin only)

Response:
{
  "success": true,
  "data": {
    "stats": [
      {
        "label": "Total courses",
        "value": 12,
        "trend": "+2 this month"
      }
    ]
  }
}
```

## Certificate Endpoints

### Get User Certificates
```
GET /api.php?action=certificates

Response:
{
  "success": true,
  "data": {
    "certificates": [
      {
        "id": 1,
        "certificate_id": "CERT-ABC123XYZ",
        "course_title": "Introduction to Web Development",
        "issued_date": "2026-08-15",
        "expiry_date": "2028-08-15",
        "status": "Active"
      }
    ]
  }
}
```

### Verify Certificate
```
GET /api.php?action=certificate&id=CERT-ABC123XYZ

Response:
{
  "success": true,
  "message": "Certificate verified.",
  "data": {
    "id": 1,
    "certificate_id": "CERT-ABC123XYZ",
    "course_title": "Introduction to Web Development",
    "student_name": "Student User",
    "issued_date": "2026-08-15",
    "expiry_date": "2028-08-15",
    "status": "Active"
  }
}
```

## Payment Endpoints

### Initiate Premium Enrollment
```
POST /api.php?action=enroll-premium
Content-Type: application/json

{
  "course_id": 2
}

Response:
{
  "success": true,
  "data": {
    "payment_id": 15,
    "amount": 79.99,
    "course": "Advanced PHP & MySQL",
    "checkout_url": "/checkout?payment_id=15"
  }
}
```

## AI Assistant Endpoints

### Get Learning Guidance
```
POST /api.php?action=ai-chat
Content-Type: application/json

{
  "question": "How do I approach this assignment about responsive design?"
}

Response:
{
  "success": true,
  "data": {
    "answer": "I recommend breaking this into a 3-step plan...",
    "suggestedAction": "Open the next milestone task..."
  }
}
```

## Dashboard Endpoint

### Get Role-Specific Dashboard
```
GET /api.php?action=dashboard&role=student
Authorization: Required

Response:
{
  "success": true,
  "data": {
    "stats": [ ... ],
    "courses": [ ... ],
    "assignments": [ ... ],
    "schedule": [ ... ],
    "analytics": { ... },
    ...
  }
}
```

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Email and password are required."
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Authentication required."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Only instructors can view this."
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Course not found."
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to load courses: Database connection error"
}
```

## Rate Limiting

- Recommended: 1000 requests per hour per user
- Implement in production using Redis or similar

## Testing with cURL

```bash
# Login
curl -X POST http://localhost:8000/api.php?action=login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@learnflow.app","password":"student123"}'

# Get courses
curl -b cookies.txt http://localhost:8000/api.php?action=courses

# Submit assignment
curl -X POST http://localhost:8000/api.php?action=submit-assignment \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"assignment_id":1,"submission_text":"My response"}'
```

## Pagination

Endpoints supporting pagination use these query parameters:

- `limit`: Number of results (default: 20, max: 100)
- `offset`: Results offset (default: 0)

Example:
```
GET /api.php?action=courses&limit=50&offset=100
```

## Response Format

All responses follow this format:

```json
{
  "success": boolean,
  "message": "Human-readable message",
  "data": {} or null
}
```

HTTP Status Codes:
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 500: Server Error
