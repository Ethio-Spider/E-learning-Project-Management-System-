<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="E-Learning Resource Repository - Discover, manage and enroll in learning resources and courses">
    <meta name="theme-color" content="#2563eb">
    <title>E-Learning Resource Repository</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="#main-content" class="sr-only">Skip to main content</a>

<header class="navbar" role="banner">
    <div class="container nav-inner">
        <a class="brand" href="#" data-section="courses" title="Go to courses">E-Learning Repository</a>
        <nav aria-label="Main navigation">
            <button class="nav-link active" type="button" data-section="courses" aria-current="page">Courses</button>
            <button class="nav-link" type="button" data-section="add-course">Add Course</button>
            <button class="nav-link" type="button" data-section="about">About</button>
        </nav>
    </div>
</header>

<main class="container main-content" id="main-content">
    <!-- Courses Section -->
    <section id="courses-section" class="section active" aria-label="Courses">
        <div class="hero">
            <div>
                <p class="eyebrow">Higher Education</p>
                <h1>E-Learning Resource Repository</h1>
                <p>Discover, manage and enroll in learning resources and courses.</p>
            </div>
            <button class="btn btn-primary" type="button" data-section="add-course">+ Add Course</button>
        </div>

        <div id="globalMessage" class="message hidden" role="status" aria-live="polite" aria-atomic="true"></div>

        <div class="toolbar">
            <label class="search-box">
                <span class="sr-only">Search courses</span>
                <input id="searchInput" type="search" placeholder="Search by title, description or category..." autocomplete="off">
            </label>
            <button id="searchButton" class="btn btn-primary" type="button" title="Search courses">Search</button>
            <select id="categoryFilter" aria-label="Filter courses by category">
                <option value="">All categories</option>
            </select>
            <select id="levelFilter" aria-label="Filter courses by difficulty level">
                <option value="">All levels</option>
                <option value="Beginner">Beginner</option>
                <option value="Intermediate">Intermediate</option>
                <option value="Advanced">Advanced</option>
            </select>
            <button id="resetButton" class="btn btn-secondary" type="button" title="Reset all filters">Reset</button>
        </div>

        <div id="projectsList" class="projects-grid" aria-live="polite" aria-label="Courses grid"></div>
    </section>

    <!-- Add/Edit Course Section -->
    <section id="add-course-section" class="section" aria-label="Add or edit course">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Course management</p>
                <h2 id="formTitle">Add New Course</h2>
            </div>
        </div>

        <div id="formMessage" class="message hidden" role="status" aria-live="polite" aria-atomic="true"></div>

        <form id="courseForm" class="form-card" novalidate>
            <input id="courseId" type="hidden">

            <div class="form-row">
                <div class="form-group">
                    <label for="title">Course title *</label>
                    <input id="title" name="title" type="text" maxlength="255" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="category">Category *</label>
                    <input id="category" name="category" type="text" maxlength="100" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="5" maxlength="5000" required></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="instructor">Instructor</label>
                    <input id="instructor" name="instructor" type="text" maxlength="255" placeholder="TBD" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input id="duration" name="duration" type="text" maxlength="100" placeholder="Self-paced" autocomplete="off">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="level">Level</label>
                    <select id="level" name="level" required>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Draft">Draft</option>
                        <option value="Archived">Archived</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="imageUrl">Image URL <span class="optional">(optional)</span></label>
                <input id="imageUrl" name="image_url" type="url" maxlength="1000" placeholder="https://example.com/course.jpg">
            </div>

            <div class="form-actions">
                <button id="saveButton" class="btn btn-primary" type="submit">Save Course</button>
                <button id="cancelEditButton" class="btn btn-secondary hidden" type="button">Cancel Edit</button>
            </div>
        </form>
    </section>

    <!-- About Section -->
    <section id="about-section" class="section" aria-label="About">
        <div class="about-card">
            <p class="eyebrow">About the project</p>
            <h2>E-Learning Resource Repository</h2>
            <p>
                This project provides a complete platform for higher-education learners
                to discover courses, view course information, manage learning resources, and enroll in available courses.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Browse and search courses by title, description, category, and difficulty level</li>
                <li>Create, edit, and delete courses with complete information</li>
                <li>Manage course resources (videos, documents, links, assignments)</li>
                <li>Enroll students with automatic duplicate prevention</li>
                <li>Track enrollment statistics and student progress</li>
                <li>Activity logging and audit trails</li>
                <li>Fully responsive design for all devices</li>
                <li>Accessible UI following WCAG standards</li>
            </ul>
            
            <h3>Technology Stack</h3>
            <ul>
                <li><strong>Backend:</strong> PHP 8+ with modern best practices</li>
                <li><strong>Database:</strong> MySQL with proper indexing and soft deletes</li>
                <li><strong>API:</strong> RESTful JSON API with comprehensive error handling</li>
                <li><strong>Frontend:</strong> Modern HTML5, CSS3, and vanilla JavaScript</li>
                <li><strong>Security:</strong> Input validation, prepared statements, CORS headers</li>
                <li><strong>Logging:</strong> Comprehensive activity and API logging system</li>
            </ul>
            
            <h3>API Endpoints</h3>
            <ul>
                <li><code>GET /api.php</code> - Get all courses</li>
                <li><code>GET /api.php?action=get&id=1</code> - Get course details</li>
                <li><code>POST /api.php?action=create</code> - Create course</li>
                <li><code>PUT /api.php?action=update&id=1</code> - Update course</li>
                <li><code>DELETE /api.php?action=delete&id=1</code> - Delete course</li>
                <li><code>POST /api.php?action=enroll</code> - Enroll student</li>
                <li>And more for resources and enrollments management</li>
            </ul>
        </div>
    </section>
</main>

<!-- Course Details Modal -->
<div id="projectModal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <button id="closeModalButton" class="close" type="button" aria-label="Close course details">×</button>
        <div id="modalBody"></div>
    </div>
</div>

<footer role="contentinfo">
    <div class="container">
        <p>&copy; <span id="year"></span> E-Learning Resource Repository | All rights reserved</p>
    </div>
</footer>

<script src="script.js" defer></script>
</body>
</html>
