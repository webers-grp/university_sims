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

