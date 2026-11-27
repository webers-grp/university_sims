-- =====================================================================
-- DATABASE: university_sims
-- PROJECT: University Student Information Management System
-- NORMALIZATION: Fully normalized to Third Normal Form (3NF)
-- TABLE COUNT: Exactly 7 relational tables
-- DEPARTMENTS: Computing, Engineering, Business, Education, Medicine
-- =====================================================================

DROP DATABASE IF EXISTS university_sims;
CREATE DATABASE university_sims CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE university_sims;

-- 1. DEPARTMENTS TABLE (Eliminates transitive dependencies from Students & Courses)
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_code VARCHAR(15) NOT NULL UNIQUE,
    department_name VARCHAR(100) NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    office_location VARCHAR(50) NOT NULL,
    tuition_fee DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. INSTRUCTORS TABLE (Eliminates transitive dependencies for Lecturers/Faculty)
CREATE TABLE instructors (
    instructor_id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_code VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    department_id INT NOT NULL,
    office_room VARCHAR(30) NOT NULL,
    employment_status ENUM('Active', 'On Leave', 'Retired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_instructor_dept FOREIGN KEY (department_id) 
        REFERENCES departments(department_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. STUDENTS TABLE (Core Student Management)
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    reg_number VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    department_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('Active', 'Suspended', 'Graduated', 'Withdrawn') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_dept FOREIGN KEY (department_id) 
        REFERENCES departments(department_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. COURSES TABLE (Core Course Management)
CREATE TABLE courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(15) NOT NULL UNIQUE,
    course_title VARCHAR(150) NOT NULL,
    credit_hours INT NOT NULL CHECK (credit_hours BETWEEN 1 AND 6),
    department_id INT NOT NULL,
    instructor_id INT NULL,
    academic_level ENUM('100', '200', '300', '400', 'Postgraduate') DEFAULT '100',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_course_dept FOREIGN KEY (department_id) 
        REFERENCES departments(department_id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_course_instructor FOREIGN KEY (instructor_id) 
        REFERENCES instructors(instructor_id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. COURSE REGISTRATIONS TABLE (Course Registration Module - Resolves M:N)
CREATE TABLE course_registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    semester ENUM('Semester 1', 'Semester 2', 'Summer') NOT NULL,
    registration_date DATE NOT NULL,
    status ENUM('Registered', 'Dropped', 'Completed') DEFAULT 'Registered',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_student_course_term UNIQUE (student_id, course_id, academic_year, semester),
    CONSTRAINT fk_reg_student FOREIGN KEY (student_id) 
        REFERENCES students(student_id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reg_course FOREIGN KEY (course_id) 
        REFERENCES courses(course_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. EXAM RESULTS TABLE (Examination Management Module)
CREATE TABLE exam_results (
    result_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL UNIQUE,
    exam_type ENUM('Main', 'Supplementary', 'Special') DEFAULT 'Main',
    marks_obtained DECIMAL(5, 2) NOT NULL,
    max_marks DECIMAL(5, 2) DEFAULT 100.00,
    exam_date DATE NOT NULL,
    remarks VARCHAR(255) NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_result_registration FOREIGN KEY (registration_id) 
        REFERENCES course_registrations(registration_id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. FEE PAYMENTS TABLE (Fee Management Module)
CREATE TABLE fee_payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    receipt_number VARCHAR(30) NOT NULL UNIQUE,
    amount_paid DECIMAL(10, 2) NOT NULL CHECK (amount_paid > 0),
    payment_date DATE NOT NULL,
    payment_method ENUM('Bank Transfer', 'Credit Card', 'Mobile Money', 'Cheque', 'Cash') NOT NULL,
    academic_year VARCHAR(10) NOT NULL,
    semester ENUM('Semester 1', 'Semester 2', 'Summer') NOT NULL,
    recorded_by VARCHAR(50) DEFAULT 'Finance_Officer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fee_student FOREIGN KEY (student_id) 
        REFERENCES students(student_id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;
