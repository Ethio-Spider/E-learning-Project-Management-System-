-- E-Learning Project Management System Database Schema
-- Run this SQL script to set up your database

CREATE DATABASE IF NOT EXISTS elearning_db;
USE elearning_db;

-- Projects/Courses Table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    instructor VARCHAR(255),
    duration VARCHAR(50),
    level VARCHAR(50) DEFAULT 'Beginner',
    status VARCHAR(50) DEFAULT 'Active',
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resources/Materials Table
CREATE TABLE IF NOT EXISTS resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50),
    file_url VARCHAR(500),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Enrolled',
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (project_id, email),
    INDEX idx_project_id (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data
INSERT INTO projects (title, description, category, instructor, duration, level, status) VALUES
('Introduction to Web Development', 'Learn HTML, CSS, and JavaScript basics', 'Web Development', 'John Doe', '8 weeks', 'Beginner', 'Active'),
('Advanced PHP & MySQL', 'Master backend development with PHP and databases', 'Backend Development', 'Jane Smith', '12 weeks', 'Intermediate', 'Active'),
('Data Science Fundamentals', 'Introduction to data analysis and visualization', 'Data Science', 'Mike Johnson', '10 weeks', 'Beginner', 'Active');
