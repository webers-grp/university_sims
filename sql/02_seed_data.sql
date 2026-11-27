USE university_sims;

-- 1. SEED DEPARTMENTS (Computing: 80k, Engineering: 80k, Business: 60k, Education: 60k, Medicine: 100k)
INSERT INTO departments (department_code, department_name, faculty, office_location, tuition_fee) VALUES
('COMP', 'Computing', 'School of Computing & Informatics', 'Technology Complex, 3rd Floor', 80000.00),
('ENG', 'Engineering', 'School of Engineering & Technology', 'Engineering Complex, 1st Floor', 80000.00),
('BUS', 'Business', 'School of Business & Economics', 'Business Tower, 2nd Floor', 60000.00),
('EDU', 'Education', 'School of Education & Humanities', 'Education Wing, Ground Floor', 60000.00),
('MED', 'Medicine', 'School of Health Sciences & Medicine', 'Medical Complex, Ground Floor', 100000.00);

