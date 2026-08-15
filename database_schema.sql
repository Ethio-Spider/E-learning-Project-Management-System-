-- Create database with UTF-8 support
CREATE DATABASE IF NOT EXISTS elearning_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE elearning_db;

-- Courses/Projects Table
CREATE TABLE IF NOT EXISTS projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    instructor VARCHAR(255) NOT NULL DEFAULT 'TBD',
    duration VARCHAR(100) NOT NULL DEFAULT 'Self-paced',
    level ENUM('Beginner', 'Intermediate', 'Advanced') NOT NULL DEFAULT 'Beginner',
    status ENUM('Active', 'Draft', 'Archived') NOT NULL DEFAULT 'Active',
    image_url VARCHAR(1000) NULL,
    price DECIMAL(10, 2) DEFAULT NULL,
    max_students INT DEFAULT NULL,
    rating DECIMAL(3, 2) DEFAULT 0,
    total_ratings INT DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_projects_category (category),
    INDEX idx_projects_level (level),
    INDEX idx_projects_status (status),
    INDEX idx_projects_created_at (created_at),
    INDEX idx_projects_instructor (instructor),
    FULLTEXT idx_projects_search (title, description)
) ENGINE=InnoDB;

-- Course Resources Table
CREATE TABLE IF NOT EXISTS resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    type ENUM('Link', 'Video', 'Document', 'Assignment', 'Quiz') NOT NULL DEFAULT 'Link',
    file_url VARCHAR(1000) NOT NULL,
    description LONGTEXT NULL,
    position INT DEFAULT 0,
    is_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_resources_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    INDEX idx_resources_project_id (project_id),
    INDEX idx_resources_type (type),
    INDEX idx_resources_position (position)
) ENGINE=InnoDB;

-- Student Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    enrollment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Enrolled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Enrolled',
    progress DECIMAL(5, 2) DEFAULT 0,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_enrollments_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_enrollment_project_email (project_id, email),
    INDEX idx_enrollments_project_id (project_id),
    INDEX idx_enrollments_email (email),
    INDEX idx_enrollments_status (status)
) ENGINE=InnoDB;

-- Activity/Progress Log Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NULL,
    enrollment_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    details LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_activity_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_activity_enrollment
        FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE CASCADE,
    INDEX idx_activity_project (project_id),
    INDEX idx_activity_action (action),
    INDEX idx_activity_created_at (created_at)
) ENGINE=InnoDB;

-- API Logs Table for debugging
CREATE TABLE IF NOT EXISTS api_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    method VARCHAR(10) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    status_code INT NOT NULL,
    response_time INT NOT NULL,
    error_message LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_logs_created_at (created_at),
    INDEX idx_api_logs_endpoint (endpoint),
    INDEX idx_api_logs_status (status_code)
) ENGINE=InnoDB;

INSERT INTO projects
    (title, description, category, instructor, duration, level, status)
SELECT
    'Introduction to Web Development',
    'Learn the fundamentals of HTML, CSS and JavaScript by building practical web pages.',
    'Web Development',
    'John Doe',
    '8 weeks',
    'Beginner',
    'Active'
WHERE NOT EXISTS (SELECT 1 FROM projects);

INSERT INTO projects
    (title, description, category, instructor, duration, level, status)
SELECT
    'Advanced PHP & MySQL',
    'Build server-side applications with PHP, MySQL, PDO and REST-style APIs.',
    'Backend Development',
    'Jane Smith',
    '12 weeks',
    'Intermediate',
    'Active'
WHERE NOT EXISTS (
    SELECT 1 FROM projects WHERE title = 'Advanced PHP & MySQL'
);

INSERT INTO projects
    (title, description, category, instructor, duration, level, status)
SELECT
    'Data Science Fundamentals',
    'Introduction to data analysis, visualization and practical data science concepts.',
    'Data Science',
    'Mike Johnson',
    '10 weeks',
    'Beginner',
    'Active'
WHERE NOT EXISTS (
    SELECT 1 FROM projects WHERE title = 'Data Science Fundamentals'
);
