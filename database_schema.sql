-- Create database with UTF-8 support
CREATE DATABASE IF NOT EXISTS elearning_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE elearning_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'instructor', 'admin') NOT NULL DEFAULT 'student',
    avatar_url VARCHAR(1000) NULL,
    bio TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB;

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

-- Email Verification Table
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    verification_token VARCHAR(255) NOT NULL UNIQUE,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_email_verifications_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_email_verifications_token (verification_token),
    INDEX idx_email_verifications_user_id (user_id),
    INDEX idx_email_verifications_expires_at (expires_at)
) ENGINE=InnoDB;

-- Password Reset Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    reset_token VARCHAR(255) NOT NULL UNIQUE,
    used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_password_resets_token (reset_token),
    INDEX idx_password_resets_user_id (user_id),
    INDEX idx_password_resets_expires_at (expires_at)
) ENGINE=InnoDB;

-- Two-Factor Authentication Secrets
CREATE TABLE IF NOT EXISTS two_factor_secrets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    secret VARCHAR(255) NOT NULL,
    method ENUM('totp', 'sms') NOT NULL DEFAULT 'totp',
    enabled BOOLEAN DEFAULT FALSE,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_2fa_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_2fa_user_id (user_id)
) ENGINE=InnoDB;

-- 2FA Backup Codes
CREATE TABLE IF NOT EXISTS backup_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    code VARCHAR(20) NOT NULL,
    used_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_backup_codes_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_backup_codes_user_id (user_id),
    UNIQUE KEY uq_backup_code (user_id, code)
) ENGINE=InnoDB;

-- Audit Logs (Extended)
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT UNSIGNED,
    old_values LONGTEXT,
    new_values LONGTEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500),
    status VARCHAR(20) DEFAULT 'success',
    details LONGTEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL,
    INDEX idx_audit_user_id (user_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_created_at (created_at)
) ENGINE=InnoDB;

-- Rate Limit Tracking
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_count INT DEFAULT 1,
    window_start TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    window_end TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rate_limit (identifier, endpoint, window_start),
    INDEX idx_rate_limit_window_end (window_end)
) ENGINE=InnoDB;

-- Password History (for security policies)
CREATE TABLE IF NOT EXISTS password_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_history_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_password_history_user_id (user_id),
    INDEX idx_password_history_changed_at (changed_at)
) ENGINE=InnoDB;

-- API Keys for Versioning
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(10) DEFAULT '2.0',
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    expires_at TIMESTAMP NULL DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_keys_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    INDEX idx_api_keys_user_id (user_id),
    INDEX idx_api_keys_hash (key_hash),
    INDEX idx_api_keys_active (is_active)
) ENGINE=InnoDB;

-- Session Management for Added Security
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_ip VARCHAR(45) NULL;

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
