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

-- Assignments Table
CREATE TABLE IF NOT EXISTS assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    due_date DATETIME NOT NULL,
    max_score INT DEFAULT 100,
    status ENUM('Open', 'Closed', 'Archived') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_assignments_project
        FOREIGN KEY (project_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    INDEX idx_assignments_project_id (project_id),
    INDEX idx_assignments_due_date (due_date),
    INDEX idx_assignments_status (status)
) ENGINE=InnoDB;

-- Assignment Submissions Table
CREATE TABLE IF NOT EXISTS submissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT UNSIGNED NOT NULL,
    enrollment_id INT UNSIGNED NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    submission_text LONGTEXT,
    file_url VARCHAR(1000),
    submitted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    score INT DEFAULT NULL,
    feedback LONGTEXT,
    graded_at TIMESTAMP NULL DEFAULT NULL,
    graded_by VARCHAR(255),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_submissions_assignment
        FOREIGN KEY (assignment_id) REFERENCES assignments(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_submissions_enrollment
        FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE CASCADE,
    UNIQUE KEY uq_submission_assignment_enrollment (assignment_id, enrollment_id),
    INDEX idx_submissions_assignment_id (assignment_id),
    INDEX idx_submissions_student_email (student_email),
    INDEX idx_submissions_submitted_at (submitted_at)
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

-- Certificates Table
CREATE TABLE IF NOT EXISTS certificates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT UNSIGNED NOT NULL,
    certificate_id VARCHAR(255) UNIQUE NOT NULL,
    course_title VARCHAR(255) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    issued_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expiry_date DATETIME,
    status ENUM('Active', 'Revoked', 'Expired') NOT NULL DEFAULT 'Active',
    verification_code VARCHAR(100),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_cert_enrollment
        FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE CASCADE,
    INDEX idx_certificates_certificate_id (certificate_id),
    INDEX idx_certificates_student_name (student_name),
    INDEX idx_certificates_status (status)
) ENGINE=InnoDB;

-- Payments/Transactions Table
CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT UNSIGNED NOT NULL,
    student_email VARCHAR(255) NOT NULL,
    course_id INT UNSIGNED NOT NULL,
    course_title VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    status ENUM('Pending', 'Completed', 'Failed', 'Refunded') NOT NULL DEFAULT 'Pending',
    payment_date TIMESTAMP,
    refund_date TIMESTAMP NULL DEFAULT NULL,
    refund_reason LONGTEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_payments_enrollment
        FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_payments_course
        FOREIGN KEY (course_id) REFERENCES projects(id)
        ON DELETE CASCADE,
    INDEX idx_payments_student_email (student_email),
    INDEX idx_payments_transaction_id (transaction_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_created_at (created_at)
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

-- Sample assignments
INSERT INTO assignments
    (project_id, title, description, due_date, max_score, status)
SELECT
    p.id,
    'Build Your First Webpage',
    'Create a personal portfolio webpage using HTML and CSS. Include at least 3 sections: header, about, and projects.',
    DATE_ADD(NOW(), INTERVAL 7 DAY),
    100,
    'Open'
FROM projects p
WHERE p.title = 'Introduction to Web Development' AND NOT EXISTS (
    SELECT 1 FROM assignments WHERE title = 'Build Your First Webpage'
);

INSERT INTO assignments
    (project_id, title, description, due_date, max_score, status)
SELECT
    p.id,
    'Responsive Design Challenge',
    'Take your portfolio website and make it responsive. It should look good on mobile, tablet, and desktop screens.',
    DATE_ADD(NOW(), INTERVAL 14 DAY),
    100,
    'Open'
FROM projects p
WHERE p.title = 'Introduction to Web Development' AND NOT EXISTS (
    SELECT 1 FROM assignments WHERE title = 'Responsive Design Challenge'
);
