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

