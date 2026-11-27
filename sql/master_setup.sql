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

USE university_sims;

-- 1. SEED DEPARTMENTS (Computing: 80k, Engineering: 80k, Business: 60k, Education: 60k, Medicine: 100k)
INSERT INTO departments (department_code, department_name, faculty, office_location, tuition_fee) VALUES
('COMP', 'Computing', 'School of Computing & Informatics', 'Technology Complex, 3rd Floor', 80000.00),
('ENG', 'Engineering', 'School of Engineering & Technology', 'Engineering Complex, 1st Floor', 80000.00),
('BUS', 'Business', 'School of Business & Economics', 'Business Tower, 2nd Floor', 60000.00),
('EDU', 'Education', 'School of Education & Humanities', 'Education Wing, Ground Floor', 60000.00),
('MED', 'Medicine', 'School of Health Sciences & Medicine', 'Medical Complex, Ground Floor', 100000.00);

-- 2. SEED INSTRUCTORS
INSERT INTO instructors (instructor_code, first_name, last_name, email, phone, department_id, office_room, employment_status) VALUES
('INS-101', 'Prof. Alan', 'Turing', 'a.turing@kyu.ac.ke', '+254-711-010101', 1, 'TC-301', 'Active'),
('INS-102', 'Dr. Nikola', 'Tesla', 'n.tesla@kyu.ac.ke', '+254-711-010102', 2, 'EC-105', 'Active'),
('INS-103', 'Prof. Adam', 'Smith', 'a.smith@kyu.ac.ke', '+254-711-010103', 3, 'BT-202', 'Active'),
('INS-104', 'Dr. Maria', 'Montessori', 'm.montessori@kyu.ac.ke', '+254-711-010104', 4, 'ED-102', 'Active'),
('INS-105', 'Dr. Florence', 'Nightingale', 'f.nightingale@kyu.ac.ke', '+254-711-010105', 5, 'MC-012', 'Active');

-- 3. SEED COURSES (All 12 required courses mapped to their departments)
INSERT INTO courses (course_code, course_title, credit_hours, department_id, instructor_id, academic_level) VALUES
-- Computing Courses (Dept 1)
('IT101', 'Information Technology', 3, 1, 1, '100'),
('CS101', 'Computer Science', 4, 1, 1, '100'),
('SE101', 'Software Engineering', 4, 1, 1, '200'),

-- Engineering Courses (Dept 2)
('CSE101', 'Computer Systems Engineering', 4, 2, 2, '100'),

-- Business Courses (Dept 3)
('BIT101', 'Business Information Technology', 3, 3, 3, '100'),
('ECO101', 'Economics', 3, 3, 3, '100'),
('FIN101', 'Finance', 3, 3, 3, '200'),

-- Education Courses (Dept 4)
('EDS101', 'Education Science', 3, 4, 4, '100'),
('EDA101', 'Education Arts', 3, 4, 4, '100'),

-- Medicine Courses (Dept 5)
('NUR101', 'Nursing', 4, 5, 5, '100'),
('CLM101', 'Clinical Medicine', 4, 5, 5, '100'),
('MED101', 'Pure Medicine', 5, 5, 5, '100');

-- 4. SEED STUDENTS (Featuring CT100/G/24900/24, Octavious Antony, and kennedy kalawa)
INSERT INTO students (reg_number, first_name, last_name, gender, date_of_birth, email, phone, address, department_id, enrollment_date, status) VALUES
('CT100/G/24900/24', 'Theo', 'Evans', 'Male', '2003-05-14', 'theoe.24900@students.kyu.ac.ke', '+254-722-249001', 'P.O. Box 100, Nairobi', 1, '2024-09-01', 'Active'),
('CT100/G/24903/24', 'Octavious', 'Antony', 'Male', '2003-05-14', 'octavious.24903@students.kyu.ac.ke', '+254-712-345678', 'P.O. Box 100, Nairobi', 1, '2024-09-01', 'Active'),
('CT100/G/23901/24', 'kennedy', 'kalawa', 'Male', '2003-08-20', 'kalawa.23901@students.kyu.ac.ke', '+254-712-345679', 'P.O. Box 001, Nairobi', 2, '2024-09-01', 'Active'),
('CT100/G/24901/24', 'Alice', 'Morgan', 'Female', '2003-04-12', 'alicem.24901@students.kyu.ac.ke', '+254-722-249002', 'P.O. Box 240, Nakuru', 5, '2024-09-01', 'Active'),
('CT100/G/24902/24', 'Brian', 'Otieno', 'Male', '2002-11-23', 'briano.24902@students.kyu.ac.ke', '+254-722-249003', 'P.O. Box 512, Kisumu', 3, '2024-09-01', 'Active'),
('CT201/G/24904/24', 'Catherine', 'Mwangi', 'Female', '2004-01-15', 'catherinem.24904@students.kyu.ac.ke', '+254-722-249004', 'P.O. Box 801, Nyeri', 4, '2024-09-01', 'Active'),
('CT301/G/24905/24', 'David', 'Kimani', 'Male', '2003-07-09', 'davidk.24905@students.kyu.ac.ke', '+254-722-249005', 'P.O. Box 143, Thika', 2, '2024-09-01', 'Active'),
('CT401/G/24906/24', 'Elena', 'Rostova', 'Female', '2002-09-30', 'elenar.24906@students.kyu.ac.ke', '+254-722-249006', 'P.O. Box 920, Mombasa', 5, '2024-09-01', 'Active');

-- 5. SEED COURSE REGISTRATIONS
INSERT INTO course_registrations (student_id, course_id, academic_year, semester, registration_date, status) VALUES
(1, 2, '2024/2025', 'Semester 1', '2024-09-05', 'Completed'), -- Theo in Computer Science
(1, 3, '2024/2025', 'Semester 1', '2024-09-05', 'Completed'), -- Theo in Software Engineering
(2, 1, '2024/2025', 'Semester 1', '2024-09-06', 'Completed'), -- Octavious in Information Technology
(3, 4, '2024/2025', 'Semester 1', '2024-09-07', 'Completed'), -- kennedy in Computer Systems Engineering
(4, 12, '2024/2025', 'Semester 1', '2024-09-08', 'Completed'), -- Alice in Pure Medicine
(5, 5, '2024/2025', 'Semester 1', '2024-09-09', 'Completed'), -- Brian in Business Information Tech
(6, 8, '2024/2025', 'Semester 1', '2024-09-10', 'Completed'), -- Catherine in Education Science
(7, 4, '2024/2025', 'Semester 1', '2024-09-11', 'Completed'), -- David in Computer Systems Engineering
(8, 11, '2024/2025', 'Semester 1', '2024-09-12', 'Completed'); -- Elena in Clinical Medicine

-- 6. SEED EXAM RESULTS
INSERT INTO exam_results (registration_id, exam_type, marks_obtained, max_marks, exam_date, remarks) VALUES
(1, 'Main', 92.50, 100.00, '2024-12-15', 'Exceptional mastery of Computer Science'),
(2, 'Main', 95.00, 100.00, '2024-12-18', 'Outstanding Software Engineering architecture'),
(3, 'Main', 84.00, 100.00, '2024-12-15', 'Very good Information Technology skills'),
(4, 'Main', 78.50, 100.00, '2024-12-17', 'Solid performance in Computer Systems Engineering'),
(5, 'Main', 91.00, 100.00, '2024-12-16', 'Distinction in Pure Medicine anatomy and clinical rotation'),
(6, 'Main', 82.50, 100.00, '2024-12-14', 'Commendable Business Information Technology practical'),
(7, 'Main', 76.00, 100.00, '2024-12-19', 'Good grasp of Education Science methodology'),
(8, 'Main', 80.00, 100.00, '2024-12-17', 'Strong engineering hardware design'),
(9, 'Main', 88.00, 100.00, '2024-12-16', 'Excellence in Clinical Medicine assessment');

-- 7. SEED FEE PAYMENTS (Reflecting 80k for Computing/Eng, 60k for Business/Edu, 100k for Medicine)
INSERT INTO fee_payments (student_id, receipt_number, amount_paid, payment_date, payment_method, academic_year, semester, recorded_by) VALUES
(1, 'RCPT-2024-001', 50000.00, '2024-08-25', 'Bank Transfer', '2024/2025', 'Semester 1', 'Finance_Sarah'),
(1, 'RCPT-2024-002', 30000.00, '2024-10-10', 'Mobile Money', '2024/2025', 'Semester 1', 'Finance_Sarah'), -- Theo Total: 80,000 (Cleared!)
(2, 'RCPT-2024-003', 45000.00, '2024-08-28', 'Bank Transfer', '2024/2025', 'Semester 1', 'Finance_John'),   -- Octavious: 45,000 (Partial: balance 35k)
(3, 'RCPT-2024-004', 50000.00, '2024-09-02', 'Mobile Money', '2024/2025', 'Semester 1', 'Finance_Sarah'),  -- kennedy: 50,000 (Partial: balance 30k)
(4, 'RCPT-2024-005', 100000.00, '2024-08-30', 'Bank Transfer', '2024/2025', 'Semester 1', 'Finance_John'), -- Alice Medicine: 100,000 (Cleared!)
(5, 'RCPT-2024-006', 60000.00, '2024-09-01', 'Bank Transfer', '2024/2025', 'Semester 1', 'Finance_Sarah'),  -- Brian Business: 60,000 (Cleared!)
(6, 'RCPT-2024-007', 35000.00, '2024-09-04', 'Mobile Money', '2024/2025', 'Semester 1', 'Finance_John'),   -- Catherine Education: 35,000 (Partial: balance 25k)
(7, 'RCPT-2024-008', 80000.00, '2024-09-03', 'Cheque', '2024/2025', 'Semester 1', 'Finance_John'),         -- David Engineering: 80,000 (Cleared!)
(8, 'RCPT-2024-009', 60000.00, '2024-09-05', 'Bank Transfer', '2024/2025', 'Semester 1', 'Finance_Sarah'); -- Elena Medicine: 60,000 (Partial: balance 40k)

USE university_sims;

-- =====================================================================
-- 1. STORED PROCEDURES
-- =====================================================================

DROP PROCEDURE IF EXISTS sp_GetStudentDetails;
DELIMITER $$
CREATE PROCEDURE sp_GetStudentDetails(IN p_student_id INT)
BEGIN
    SELECT 
        s.student_id,
        s.reg_number,
        CONCAT(s.first_name, ' ', s.last_name) AS full_name,
        s.gender,
        s.email,
        s.phone,
        s.address,
        d.department_name,
        d.faculty,
        d.tuition_fee AS expected_tuition,
        s.enrollment_date,
        s.status,
        (SELECT COUNT(*) FROM course_registrations cr WHERE cr.student_id = s.student_id) AS total_courses_enrolled,
        (SELECT COALESCE(SUM(amount_paid), 0.00) FROM fee_payments fp WHERE fp.student_id = s.student_id) AS total_fees_paid,
        (d.tuition_fee - (SELECT COALESCE(SUM(amount_paid), 0.00) FROM fee_payments fp WHERE fp.student_id = s.student_id)) AS balance
    FROM students s
    JOIN departments d ON s.department_id = d.department_id
    WHERE s.student_id = p_student_id;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_RegisterStudentCourse;
DELIMITER $$
CREATE PROCEDURE sp_RegisterStudentCourse(
    IN p_student_id INT,
    IN p_course_id INT,
    IN p_academic_year VARCHAR(10),
    IN p_semester VARCHAR(20),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT DEFAULT 0;
    
    -- Check if student is already registered for this course in the same term
    SELECT COUNT(*) INTO v_exists 
    FROM course_registrations
    WHERE student_id = p_student_id 
      AND course_id = p_course_id 
      AND academic_year = p_academic_year 
      AND semester = p_semester;
      
    IF v_exists > 0 THEN
        SET p_message = 'ERROR: Student is already registered for this course in this term.';
    ELSE
        INSERT INTO course_registrations (student_id, course_id, academic_year, semester, registration_date, status)
        VALUES (p_student_id, p_course_id, p_academic_year, p_semester, CURDATE(), 'Registered');
        SET p_message = 'SUCCESS: Course registered successfully.';
    END IF;
END$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_RecordFeePayment;
DELIMITER $$
CREATE PROCEDURE sp_RecordFeePayment(
    IN p_student_id INT,
    IN p_amount DECIMAL(10,2),
    IN p_method VARCHAR(30),
    IN p_year VARCHAR(10),
    IN p_semester VARCHAR(20),
    IN p_officer VARCHAR(50),
    OUT p_receipt VARCHAR(30)
)
BEGIN
    SET p_receipt = CONCAT('RCPT-', DATE_FORMAT(NOW(), '%Y%m%d'), '-', LPAD(FLOOR(RAND() * 9999), 4, '0'));
    
    INSERT INTO fee_payments (student_id, receipt_number, amount_paid, payment_date, payment_method, academic_year, semester, recorded_by)
    VALUES (p_student_id, p_receipt, p_amount, CURDATE(), p_method, p_year, p_semester, p_officer);
END$$
DELIMITER ;

-- =====================================================================
-- 2. TRIGGERS
-- =====================================================================

DROP TRIGGER IF EXISTS trg_validate_exam_marks_insert;
DELIMITER $$
CREATE TRIGGER trg_validate_exam_marks_insert
BEFORE INSERT ON exam_results
FOR EACH ROW
BEGIN
    IF NEW.marks_obtained < 0.00 OR NEW.marks_obtained > 100.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Trigger Exception: Examination marks must be strictly between 0 and 100.';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_validate_exam_marks_update;
DELIMITER $$
CREATE TRIGGER trg_validate_exam_marks_update
BEFORE UPDATE ON exam_results
FOR EACH ROW
BEGIN
    IF NEW.marks_obtained < 0.00 OR NEW.marks_obtained > 100.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Trigger Exception: Examination marks must be strictly between 0 and 100.';
    END IF;
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS trg_update_registration_status;
DELIMITER $$
CREATE TRIGGER trg_update_registration_status
AFTER INSERT ON exam_results
FOR EACH ROW
BEGIN
    UPDATE course_registrations
    SET status = 'Completed'
    WHERE registration_id = NEW.registration_id;
END$$
DELIMITER ;

-- =====================================================================
-- 3. VIEWS
-- =====================================================================

DROP VIEW IF EXISTS vw_StudentExamResults;
CREATE VIEW vw_StudentExamResults AS
SELECT 
    er.result_id,
    s.student_id,
    s.reg_number,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    c.course_code,
    c.course_title,
    c.credit_hours,
    cr.academic_year,
    cr.semester,
    er.exam_type,
    er.marks_obtained,
    CASE 
        WHEN er.marks_obtained >= 70 THEN 'A'
        WHEN er.marks_obtained >= 60 THEN 'B'
        WHEN er.marks_obtained >= 50 THEN 'C'
        WHEN er.marks_obtained >= 40 THEN 'D'
        ELSE 'F'
    END AS letter_grade,
    CASE 
        WHEN er.marks_obtained >= 50 THEN 'Pass'
        ELSE 'Fail'
    END AS result_status,
    er.exam_date,
    er.remarks
FROM exam_results er
JOIN course_registrations cr ON er.registration_id = cr.registration_id
JOIN students s ON cr.student_id = s.student_id
JOIN courses c ON cr.course_id = c.course_id;

DROP VIEW IF EXISTS vw_StudentFeeBalances;
CREATE VIEW vw_StudentFeeBalances AS
SELECT 
    s.student_id,
    s.reg_number,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    d.department_name,
    d.tuition_fee AS expected_tuition,
    COALESCE(SUM(fp.amount_paid), 0.00) AS total_amount_paid,
    (d.tuition_fee - COALESCE(SUM(fp.amount_paid), 0.00)) AS outstanding_balance,
    CASE 
        WHEN COALESCE(SUM(fp.amount_paid), 0.00) >= d.tuition_fee THEN 'Cleared'
        WHEN COALESCE(SUM(fp.amount_paid), 0.00) > 0 THEN 'Partial'
        ELSE 'Unpaid'
    END AS payment_status
FROM students s
JOIN departments d ON s.department_id = d.department_id
LEFT JOIN fee_payments fp ON s.student_id = fp.student_id
GROUP BY s.student_id, s.reg_number, s.first_name, s.last_name, d.department_name, d.tuition_fee;

DROP VIEW IF EXISTS vw_CourseEnrollmentSummary;
CREATE VIEW vw_CourseEnrollmentSummary AS
SELECT 
    c.course_id,
    c.course_code,
    c.course_title,
    c.credit_hours,
    d.department_name,
    CONCAT(COALESCE(i.first_name, 'Unassigned'), ' ', COALESCE(i.last_name, '')) AS instructor_name,
    COUNT(cr.registration_id) AS total_enrolled,
    ROUND(AVG(er.marks_obtained), 2) AS average_score
FROM courses c
JOIN departments d ON c.department_id = d.department_id
LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
LEFT JOIN course_registrations cr ON c.course_id = cr.course_id
LEFT JOIN exam_results er ON cr.registration_id = er.registration_id
GROUP BY c.course_id, c.course_code, c.course_title, c.credit_hours, d.department_name, i.first_name, i.last_name;

-- =====================================================================
-- 4. INDEXING (IDEMPOTENT CREATION)
-- =====================================================================

CREATE OR REPLACE INDEX idx_student_name ON students (last_name, first_name);
CREATE OR REPLACE INDEX idx_student_email ON students (email);
CREATE OR REPLACE INDEX idx_student_dept ON students (department_id);
CREATE OR REPLACE INDEX idx_course_dept ON courses (department_id);
CREATE OR REPLACE INDEX idx_reg_student_term ON course_registrations (student_id, academic_year, semester);
CREATE OR REPLACE INDEX idx_fee_student_date ON fee_payments (student_id, payment_date);

-- =====================================================================
-- 5. DATABASE SECURITY (ROLES & USERS)
-- =====================================================================

CREATE ROLE IF NOT EXISTS 'admin_role';
CREATE ROLE IF NOT EXISTS 'lecturer_role';

GRANT ALL PRIVILEGES ON university_sims.* TO 'admin_role';

GRANT SELECT ON university_sims.students TO 'lecturer_role';
GRANT SELECT ON university_sims.courses TO 'lecturer_role';
GRANT SELECT ON university_sims.departments TO 'lecturer_role';
GRANT SELECT ON university_sims.course_registrations TO 'lecturer_role';
GRANT SELECT, INSERT, UPDATE ON university_sims.exam_results TO 'lecturer_role';
GRANT SELECT ON university_sims.vw_StudentExamResults TO 'lecturer_role';
GRANT SELECT ON university_sims.vw_CourseEnrollmentSummary TO 'lecturer_role';

CREATE USER IF NOT EXISTS 'admin_user'@'localhost' IDENTIFIED BY 'Admin@Secure2024';
CREATE USER IF NOT EXISTS 'lecturer_user'@'localhost' IDENTIFIED BY 'Lecturer@Secure2024';

GRANT 'admin_role' TO 'admin_user'@'localhost';
GRANT 'lecturer_role' TO 'lecturer_user'@'localhost';

SET DEFAULT ROLE 'admin_role' FOR 'admin_user'@'localhost';
SET DEFAULT ROLE 'lecturer_role' FOR 'lecturer_user'@'localhost';

FLUSH PRIVILEGES;
