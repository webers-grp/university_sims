#!/usr/bin/env python3
"""
UniSIMS Presentation Generator
Creates a professional 16:9 widescreen PowerPoint presentation (.pptx)
with executive dark-slate styling, visual cards, structured tables,
and integrated Speaker Notes + Examiner Defense Q&A on every slide.
"""

import sys
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE

# Design Theme Palette
BG_DARK = RGBColor(15, 23, 42)        # #0F172A Deep Navy
CARD_BG = RGBColor(30, 41, 59)        # #1E293B Slate Card
CARD_INNER = RGBColor(19, 27, 46)     # #131B2E Darker inner box
ACCENT_BLUE = RGBColor(59, 130, 246)  # #3B82F6 Royal Blue
ACCENT_CYAN = RGBColor(6, 182, 212)   # #06B6D4 Electric Cyan
ACCENT_EMERALD = RGBColor(16, 185, 129)# #10B981 Emerald Green
ACCENT_AMBER = RGBColor(245, 158, 11) # #F59E0B Amber
ACCENT_ROSE = RGBColor(244, 63, 94)   # #F43F5E Rose Red
ACCENT_PURPLE = RGBColor(139, 92, 246)# #8B5CF6 Purple
TEXT_WHITE = RGBColor(248, 250, 252)  # #F8FAFC Pure Light
TEXT_MUTED = RGBColor(148, 163, 184)  # #94A3B8 Slate Gray
BORDER_COLOR = RGBColor(51, 65, 85)   # #334155 Slate Border

def create_presentation():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    def set_background(slide):
        bg = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
        bg.fill.solid()
        bg.fill.fore_color.rgb = BG_DARK
        bg.line.fill.background() # No border
        return bg

    def add_header(slide, tag, title, subtitle):
        # Category Tag
        tag_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.7), Inches(0.35))
        tf_tag = tag_box.text_frame
        tf_tag.word_wrap = True
        tf_tag.margin_left = tf_tag.margin_top = tf_tag.margin_right = tf_tag.margin_bottom = 0
        p_tag = tf_tag.paragraphs[0]
        p_tag.text = tag.upper()
        p_tag.font.size = Pt(10)
        p_tag.font.bold = True
        p_tag.font.color.rgb = ACCENT_CYAN

        # Title
        title_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.75), Inches(11.7), Inches(0.6))
        tf_title = title_box.text_frame
        tf_title.word_wrap = True
        tf_title.margin_left = tf_title.margin_top = tf_title.margin_right = tf_title.margin_bottom = 0
        p_title = tf_title.paragraphs[0]
        p_title.text = title
        p_title.font.size = Pt(22)
        p_title.font.bold = True
        p_title.font.color.rgb = TEXT_WHITE

        # Subtitle
        if subtitle:
            sub_box = slide.shapes.add_textbox(Inches(0.8), Inches(1.35), Inches(11.7), Inches(0.45))
            tf_sub = sub_box.text_frame
            tf_sub.word_wrap = True
            tf_sub.margin_left = tf_sub.margin_top = tf_sub.margin_right = tf_sub.margin_bottom = 0
            p_sub = tf_sub.paragraphs[0]
            p_sub.text = subtitle
            p_sub.font.size = Pt(12)
            p_sub.font.color.rgb = TEXT_MUTED

    def set_speaker_notes(slide, script, q_and_a):
        notes = slide.notes_slide.notes_text_frame
        notes.text = f"=== WHAT TO SAY ON THIS SLIDE ===\n{script}\n\n=== EXAMINER DEFENSE Q&A ===\nQ: {q_and_a[0]}\nA: {q_and_a[1]}"

    def add_card(slide, left, top, width, height, title, items, border_color=BORDER_COLOR, bg_color=CARD_BG, title_color=TEXT_WHITE):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(left), Inches(top), Inches(width), Inches(height))
        card.fill.solid()
        card.fill.fore_color.rgb = bg_color
        card.line.color.rgb = border_color
        card.line.width = Pt(1.5)

        tb = slide.shapes.add_textbox(Inches(left + 0.2), Inches(top + 0.15), Inches(width - 0.4), Inches(height - 0.3))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0

        # Title
        p0 = tf.paragraphs[0]
        p0.text = title
        p0.font.size = Pt(13)
        p0.font.bold = True
        p0.font.color.rgb = title_color
        p0.space_after = Pt(8)

        # Bullets
        for itm in items:
            p = tf.add_paragraph()
            p.text = f"• {itm}"
            p.font.size = Pt(10.5)
            p.font.color.rgb = TEXT_MUTED
            p.space_after = Pt(4)

        return card

    # ==========================================
    # SLIDE 1: Title Slide
    # ==========================================
    s1 = prs.slides.add_slide(blank_layout)
    set_background(s1)

    # Accent decorative bar
    bar = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.8), Inches(0.15), Inches(3.4))
    bar.fill.solid()
    bar.fill.fore_color.rgb = ACCENT_BLUE
    bar.line.fill.background()

    # Title Box
    t_box = s1.shapes.add_textbox(Inches(1.2), Inches(1.7), Inches(11.2), Inches(3.6))
    tf1 = t_box.text_frame
    tf1.word_wrap = True

    p_tag = tf1.paragraphs[0]
    p_tag.text = "ADVANCED DBMS CAPSTONE DEFENSE"
    p_tag.font.size = Pt(12)
    p_tag.font.bold = True
    p_tag.font.color.rgb = ACCENT_CYAN
    p_tag.space_after = Pt(10)

    p_main = tf1.add_paragraph()
    p_main.text = "UniSIMS: University Student Information Management System"
    p_main.font.size = Pt(28)
    p_main.font.bold = True
    p_main.font.color.rgb = TEXT_WHITE
    p_main.space_after = Pt(12)

    p_desc = tf1.add_paragraph()
    p_desc.text = "A 3NF Relational Architecture with Active Triggers, Parameterized Stored Procedures, Composite B-Tree Indexing, and Enterprise Disaster Recovery on MariaDB."
    p_desc.font.size = Pt(14)
    p_desc.font.color.rgb = TEXT_MUTED
    p_desc.space_after = Pt(20)

    p_team = tf1.add_paragraph()
    p_team.text = "Presented by: Kennedy Kalawa & Capstone Team | Academic Year 2026/2027"
    p_team.font.size = Pt(12)
    p_team.font.bold = True
    p_team.font.color.rgb = ACCENT_EMERALD

    # 4 Quick Metric Cards
    add_card(s1, 0.8, 5.5, 2.7, 1.4, "Database Engine", ["MariaDB 10.4+", "InnoDB Storage Engine", "ACID Compliant"], ACCENT_BLUE)
    add_card(s1, 3.8, 5.5, 2.7, 1.4, "Relational Model", ["Strict 3NF Schema", "7 Normalized Tables", "Zero Redundancy"], ACCENT_CYAN)
    add_card(s1, 6.8, 5.5, 2.7, 1.4, "Advanced Features", ["Boundary Triggers", "3 Stored Procedures", "Analytical Views"], ACCENT_EMERALD)
    add_card(s1, 9.8, 5.5, 2.7, 1.4, "Application Stack", ["PHP 8.2 PDO Layer", "Tailwind Web UI", "Real-time Live Sync"], ACCENT_AMBER)

    set_speaker_notes(s1,
        "Welcome the examiners and panel. State that UniSIMS addresses academic administration by enforcing rules in the database engine itself rather than relying solely on web application validation.",
        ("What makes UniSIMS distinct from simple web forms?",
         "UniSIMS implements business logic inside MariaDB: boundary checks via triggers, atomic balance updates via stored procedures, and security via engine-level RBAC roles."))

    # ==========================================
    # SLIDE 2: Problem Statement & Objectives
    # ==========================================
    s2 = prs.slides.add_slide(blank_layout)
    set_background(s2)
    add_header(s2, "Institutional Context", "Problem Statement & Strategic Objectives",
               "Analyzing legacy operational bottlenecks and defining the engineering criteria for UniSIMS.")

    add_card(s2, 0.8, 2.0, 5.6, 4.8, "Legacy Administrative Bottlenecks", [
        "Data Redundancy & Update Anomalies: Student data duplicated across departments leading to desynchronization.",
        "Examination Integrity Vulnerabilities: Marks entered without database boundary constraints (risk of scores > 100 or < 0).",
        "Disjointed Fee Accounting: Finance offices disconnected from exam clearances, causing delayed registration permits.",
        "Performance Degradation: Unindexed full table scans causing slow load times during peak semester enrollment.",
        "Security Blindspots: Lack of database Role-Based Access Control (RBAC), leaving tables open to unauthorized edits."
    ], ACCENT_ROSE)

    add_card(s2, 6.8, 2.0, 5.6, 4.8, "UniSIMS Engineering Solutions", [
        "Strict 3NF Normalization: Exactly 7 relational entities eliminating insertion, deletion, and update anomalies.",
        "Active Engine Constraints: Triggers intercept invalid examination marks before persistence to disk.",
        "Real-Time Ledger Sync: Relational views calculate student tuition balances dynamically in Kenyan Shillings (KSh).",
        "B-Tree Composite Indexing: Transforms linear O(n) table scans into logarithmic O(log n) lookups.",
        "Enterprise Disaster Recovery: Automated hot backup and verification restore pipeline guaranteeing zero data loss."
    ], ACCENT_EMERALD)

    set_speaker_notes(s2,
        "Contrast legacy manual and flat-file systems with the UniSIMS database-centric design. Highlight data anomalies and exam tampering risks that UniSIMS eliminates.",
        ("Why use a relational database instead of NoSQL?",
         "Academic and financial ledgers require strict ACID guarantees and foreign key constraints (CASCADE/RESTRICT) to prevent orphan records, which NoSQL does not guarantee without complex application code."))

    # ==========================================
    # SLIDE 3: 3-Tier System Architecture
    # ==========================================
    s3 = prs.slides.add_slide(blank_layout)
    set_background(s3)
    add_header(s3, "System Engineering", "Enterprise 3-Tier Architecture",
               "Decoupled architecture ensuring modularity, horizontal scalability, and multi-layered defense.")

    add_card(s3, 0.8, 2.0, 3.6, 4.8, "Tier 1: Presentation Layer", [
        "Modern Responsive Web Interface.",
        "Tailwind CSS aesthetic with dark/light visual clarity.",
        "Zero external graphic dependencies (Lucide SVG vector icons).",
        "Real-time interactive modals for admissions, course enrollment, and payments.",
        "Dedicated ADBMS Lab testing portal for live trigger and index audits."
    ], ACCENT_BLUE)

    add_card(s3, 4.8, 2.0, 3.6, 4.8, "Tier 2: Application Data Layer", [
        "PHP 8.2 Universal PDO Connector (public/db.php).",
        "Auto-detects XAMPP Unix sockets and standard TCP ports.",
        "100% Parameterized queries with prepared statements (zero SQL injection).",
        "Strict transaction wrappers (beginTransaction, commit, rollBack).",
        "Custom exception interceptors capturing MariaDB SQLSTATE errors."
    ], ACCENT_CYAN)

    add_card(s3, 8.8, 2.0, 3.6, 4.8, "Tier 3: MariaDB 3NF Engine", [
        "InnoDB transactional storage engine.",
        "7 Relational tables in strict 3NF.",
        "Active triggers enforcing business rules.",
        "Pre-compiled parameterized stored procedures.",
        "Pre-aggregated analytical views.",
        "B-Tree composite indexes for O(log n) queries."
    ], ACCENT_EMERALD)

    set_speaker_notes(s3,
        "Walk the panel through each tier. Emphasize that Tier 2 strictly uses PDO prepared statements to eliminate SQL injection, while Tier 3 houses the actual intelligence (triggers, procedures, and views).",
        ("How does the system handle database connection drops?",
         "public/db.php implements a dual socket/TCP probe with automated fallback and graceful PDOException error reporting."))

    # ==========================================
    # SLIDE 4: Database Normalization (3NF Proof)
    # ==========================================
    s4 = prs.slides.add_slide(blank_layout)
    set_background(s4)
    add_header(s4, "Relational Theory", "Database Normalization Journey: 0NF → 3NF",
               "Step-by-step mathematical decomposition eliminating all functional dependencies on non-prime attributes.")

    add_card(s4, 0.8, 2.0, 2.7, 4.8, "0NF (Unnormalized)", [
        "Single flat table.",
        "Repeating course groups.",
        "Multi-valued exam scores.",
        "Department heads repeated.",
        "Insertion Anomaly: Cannot create a department without enrolling a student.",
        "Deletion Anomaly: Deleting last student wipes course info."
    ], ACCENT_ROSE)

    add_card(s4, 3.8, 2.0, 2.7, 4.8, "1NF (Atomic Values)", [
        "Eliminated all repeating groups.",
        "Every column holds atomic, indivisible scalar data.",
        "Primary keys defined for each record.",
        "Limitation: Still contains composite primary keys leading to partial functional dependencies."
    ], ACCENT_AMBER)

    add_card(s4, 6.8, 2.0, 2.7, 4.8, "2NF (Full Functional Dep)", [
        "Achieved 1NF compliance.",
        "All non-key attributes depend fully on whole candidate keys.",
        "Decomposed registrations and examinations.",
        "Limitation: Transitive dependencies still exist (student_id -> course_id -> department)."
    ], ACCENT_BLUE)

    add_card(s4, 9.8, 2.0, 2.7, 4.8, "3NF (Zero Transitive Dep)", [
        "Achieved 2NF compliance.",
        "Eliminated all transitive dependencies (X -> Y where X is superkey).",
        "Departments and courses separated into dedicated lookup entities.",
        "Exactly 7 normalized tables linked solely via Foreign Keys."
    ], ACCENT_EMERALD)

    set_speaker_notes(s4,
        "This is a core academic slide. Clearly explain: 0NF had repeating groups; 1NF made attributes atomic; 2NF eliminated partial dependencies; 3NF eliminated transitive dependencies like student -> course -> department.",
        ("Can you give a concrete example of a transitive dependency you resolved?",
         "In 2NF, student_id determined course_id, which determined department_name. Updating department name meant modifying thousands of student rows. In 3NF, departments has its own table, referenced cleanly by ID."))

    # ==========================================
    # SLIDE 5: 7 Core Tables & ERD Relationships
    # ==========================================
    s5 = prs.slides.add_slide(blank_layout)
    set_background(s5)
    add_header(s5, "Relational Model", "The 7 Core Database Tables & Cardinalities",
               "Formal entity breakdown enforcing referential integrity with cascading updates and deletions.")

    # Table layout
    add_card(s5, 0.8, 2.0, 3.6, 2.3, "1. departments (1:M to Courses)", [
        "PK: department_id (INT AUTO_INCREMENT)",
        "Attributes: department_name (UNIQUE), faculty, office_location",
        "Zero redundancy in departmental metadata."
    ], ACCENT_BLUE)

    add_card(s5, 4.8, 2.0, 3.6, 2.3, "2. courses (M:1 to Dept, 1:M to Students)", [
        "PK: course_id | FK: department_id",
        "Attributes: course_code (UNIQUE), course_name, credits, tuition_fee",
        "Tuition fee stored as DECIMAL(10,2) in KSh."
    ], ACCENT_CYAN)

    add_card(s5, 8.8, 2.0, 3.6, 2.3, "3. instructors (M:1 to Dept)", [
        "PK: instructor_id | FK: department_id",
        "Attributes: first_name, last_name, email, office_phone",
        "Faculty directory mapped to specific faculties."
    ], ACCENT_PURPLE)

    add_card(s5, 0.8, 4.5, 3.6, 2.3, "4. students (M:1 to Course, 1:M to Reg)", [
        "PK: student_id | FK: course_id",
        "Attributes: reg_number (UNIQUE), name, email, year_of_study, status",
        "Standard reg format: CT100/G/24900/24."
    ], ACCENT_EMERALD)

    add_card(s5, 4.8, 4.5, 3.6, 2.3, "5. registrations (M:1 to Student & Course)", [
        "PK: registration_id | FK: student_id, course_id",
        "Attributes: academic_year, semester, registration_date, status",
        "Tracks semester enrollment pipeline."
    ], ACCENT_AMBER)

    add_card(s5, 8.8, 4.5, 3.6, 2.3, "6. examinations & 7. fee_payments", [
        "examinations: PK exam_id, FK registration_id, marks (0-100), grade (A-F)",
        "fee_payments: PK payment_id, FK student_id, amount_paid (KSh), receipt_number",
        "Full financial ledger and academic grading audit trail."
    ], ACCENT_ROSE)

    set_speaker_notes(s5,
        "Walk through the 7 entities and explain their relationships. Point out that registrations acts as the associative entity between students and courses, which connects to examinations.",
        ("Why are registrations and examinations separate tables?",
         "Students register at the start of a semester, but sit exams months later. Combining them would force NULL values for grades all semester. Normalization separates unit enrollment from grading."))

    # ==========================================
    # SLIDE 6: 5 Departments & Fee Schedule
    # ==========================================
    s6 = prs.slides.add_slide(blank_layout)
    set_background(s6)
    add_header(s6, "Institutional Configuration", "Departmental Hierarchy & Differential Fee Schedule",
               "Academic curriculum mapping across 5 departments, 12 specialized degree programs, and tier-based tuition in KSh.")

    # Table Shape
    table_shape = s6.shapes.add_table(6, 4, Inches(0.8), Inches(2.0), Inches(11.7), Inches(3.6))
    table = table_shape.table
    table.columns[0].width = Inches(2.2)
    table.columns[1].width = Inches(5.5)
    table.columns[2].width = Inches(1.8)
    table.columns[3].width = Inches(2.2)

    headers = ["Department", "Degree Courses Offered", "Semester Credits", "Tuition Fee / Sem"]
    for i, h in enumerate(headers):
        cell = table.cell(0, i)
        cell.fill.solid()
        cell.fill.fore_color.rgb = CARD_BG
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.size = Pt(11)
        p.font.bold = True
        p.font.color.rgb = ACCENT_CYAN

    data = [
        ("Computing", "Information Technology, Computer Science, Software Engineering, Computer Systems Engineering", "16 Credits", "KSh 80,000"),
        ("Engineering", "Computer Systems Engineering (Dual), Mechanical, Telecommunications", "18 Credits", "KSh 80,000"),
        ("Business", "Business Information Technology, Economics, Finance", "14 Credits", "KSh 60,000"),
        ("Education", "Education Science, Education Arts", "15 Credits", "KSh 60,000"),
        ("Medicine", "Nursing, Clinical Medicine, Pure Medicine (MBChB)", "20 Credits", "KSh 100,000")
    ]

    for row_idx, row_data in enumerate(data, start=1):
        for col_idx, text in enumerate(row_data):
            cell = table.cell(row_idx, col_idx)
            cell.fill.solid()
            cell.fill.fore_color.rgb = CARD_INNER
            p = cell.text_frame.paragraphs[0]
            p.text = text
            p.font.size = Pt(10)
            if col_idx == 3:
                p.font.bold = True
                p.font.color.rgb = ACCENT_EMERALD
            elif col_idx == 0:
                p.font.bold = True
                p.font.color.rgb = TEXT_WHITE
            else:
                p.font.color.rgb = TEXT_MUTED

    add_card(s6, 0.8, 5.8, 11.7, 1.1, "Standardized Institutional Data Formats", [
        "Registration Format: CT100/G/24900/24 (Standard university admission convention)",
        "Student Email Protocol: firstname.adm@students.kyu.ac.ke | Currency: Strictly Kenyan Shillings (KSh)"
    ], ACCENT_CYAN)

    set_speaker_notes(s6,
        "Present the exact curriculum structure: 5 departments, 12 degree programs, and differential tuition fees in Kenyan Shillings (KSh 80,000 for Computing/Engineering, KSh 60,000 for Business/Education, KSh 100,000 for Medicine).",
        ("How does the database calculate total fees owed by a student?",
         "The courses table stores the semester fee rate as DECIMAL(10,2). The view vw_StudentFeeBalances aggregates course fees against sum of payments in fee_payments, computing net arrears dynamically."))

    # ==========================================
    # SLIDE 7: 5 Functional Modules
    # ==========================================
    s7 = prs.slides.add_slide(blank_layout)
    set_background(s7)
    add_header(s7, "System Modules", "Five Integrated Core Academic Modules",
               "End-to-end institutional workflow connecting admissions, curriculum, enrollment, grading, and accounting.")

    add_card(s7, 0.8, 2.0, 3.6, 2.3, "1. Student Management", [
        "Student registration & bio-data capture.",
        "Automated reg number generation.",
        "Status tracking (Active, Suspended, Graduated).",
        "Detailed 360-degree student profile modal."
    ], ACCENT_BLUE)

    add_card(s7, 4.8, 2.0, 3.6, 2.3, "2. Course & Curriculum", [
        "Catalog of 12 degree courses across 5 depts.",
        "Credit hour weighting (12 - 20 credits).",
        "Tuition schedule configuration in KSh.",
        "Faculty leadership assignment."
    ], ACCENT_CYAN)

    add_card(s7, 8.8, 2.0, 3.6, 2.3, "3. Course Registration", [
        "Semester-based unit booking.",
        "Duplicate enrollment validation.",
        "Add/Drop status workflows.",
        "Audit history of registered semesters."
    ], ACCENT_PURPLE)

    add_card(s7, 0.8, 4.5, 3.6, 2.3, "4. Examination Grading", [
        "Continuous assessment and final scores.",
        "Standard 5-tier grading (A, B, C, D, F).",
        "Academic standing and GPA calculation.",
        "Trigger-enforced boundary defense (0 - 100)."
    ], ACCENT_AMBER)

    add_card(s7, 4.8, 4.5, 3.6, 2.3, "5. Fee Accounting", [
        "Tuition debit generation per semester.",
        "Payment recording with unique receipt serials.",
        "Real-time arrears calculation (Billed - Paid).",
        "Exam clearance status determination."
    ], ACCENT_EMERALD)

    add_card(s7, 8.8, 4.5, 3.6, 2.3, "Real-Time MariaDB Sync", [
        "Every page interacts live with MariaDB.",
        "Zero static JSON caching or stale data.",
        "Changes in phpMyAdmin reflect on UI instantly.",
        "ACID guarantees across all modules."
    ], ACCENT_ROSE)

    set_speaker_notes(s7,
        "Briefly outline each module. Stress that all 5 modules interact directly with MariaDB via PDO prepared statements, so data modified in phpMyAdmin or the UI synchronizes in real time.",
        ("How does fee payment affect exam clearance?",
         "Module 5 checks vw_StudentFeeBalances. If arrears equal zero, the student's status allows exam card generation; if balance is outstanding, registration remains locked."))

    # ==========================================
    # SLIDE 8: Advanced DBMS: Database Triggers
    # ==========================================
    s8 = prs.slides.add_slide(blank_layout)
    set_background(s8)
    add_header(s8, "ADBMS Feature 1", "Active Database Triggers: Kernel-Level Integrity",
               "Automating boundary validation and lifecycle state changes before commits reach persistent disk.")

    add_card(s8, 0.8, 2.0, 5.6, 4.8, "Trigger 1: trg_CheckExamMarksBoundary", [
        "Enforces exam score integrity at the engine level.",
        "Runs BEFORE INSERT and BEFORE UPDATE on examinations.",
        "Intercepts invalid scores: marks_obtained < 0 OR > 100.",
        "Throws custom exception via SIGNAL SQLSTATE '45000'.",
        "Guarantees that no instructor, script, or API can corrupt marks.",
        "Tested live in the ADBMS Lab with instant exception catch."
    ], ACCENT_ROSE)

    add_card(s8, 6.8, 2.0, 5.6, 4.8, "Trigger 2: trg_UpdateRegistrationStatus", [
        "Maintains academic enrollment lifecycle automatically.",
        "Runs AFTER INSERT on the examinations table.",
        "Detects when final examination marks are submitted.",
        "Automatically updates status = 'completed' on registrations.",
        "Eliminates manual registrar intervention and human error.",
        "Guarantees multi-table data consistency."
    ], ACCENT_CYAN)

    set_speaker_notes(s8,
        "Explain triggers as engine-level active rules. Walk through trg_CheckExamMarksBoundary, explaining how it uses SIGNAL SQLSTATE '45000' to roll back out-of-bounds marks like -10 or 150.",
        ("Why enforce marks boundary in a database trigger instead of PHP?",
         "Application validation can be bypassed by direct SQL queries, import scripts, or API bugs. Enforcing it at the trigger level guarantees 100% data integrity regardless of how the database is accessed."))

    # ==========================================
    # SLIDE 9: Advanced DBMS: Stored Procedures
    # ==========================================
    s9 = prs.slides.add_slide(blank_layout)
    set_background(s9)
    add_header(s9, "ADBMS Feature 2", "Parameterized Stored Procedures",
               "Pre-compiled transactional server routines encapsulating business logic and minimizing network round-trips.")

    add_card(s9, 0.8, 2.0, 3.6, 4.8, "sp_GetStudentDetails", [
        "Input Parameter: IN p_student_id INT",
        "Executes an optimized 3-table JOIN across students, courses, and departments.",
        "Retrieves academic standing, degree, and faculty in a single network round-trip.",
        "Reduces application-to-database latency.",
        "Execution syntax: CALL sp_GetStudentDetails(1);"
    ], ACCENT_BLUE)

    add_card(s9, 4.8, 2.0, 3.6, 4.8, "sp_RegisterStudentCourse", [
        "Input Parameters: student_id, course_id, academic_year, semester",
        "Checks for duplicate semester enrollment before insertion.",
        "Validates active student standing.",
        "Atomically creates registration record.",
        "Execution syntax: CALL sp_RegisterStudentCourse(1, 2, '2026/2027', 1);"
    ], ACCENT_CYAN)

    add_card(s9, 8.8, 2.0, 3.6, 4.8, "sp_RecordFeePayment", [
        "Input Parameters: student_id, amount_paid, payment_method, receipt_number",
        "Wraps transaction in ACID boundary.",
        "Generates unique financial receipt.",
        "Updates payment ledgers atomically.",
        "Rolls back automatically on receipt collisions.",
        "Execution syntax: CALL sp_RecordFeePayment(1, 40000.00, 'Bank', 'RCT-101');"
    ], ACCENT_EMERALD)

    set_speaker_notes(s9,
        "Highlight the 3 stored procedures. Explain how sp_RecordFeePayment wraps financial transactions atomically, preventing half-completed ledger writes if a network failure occurs.",
        ("What are the advantages of stored procedures over application SQL?",
         "Stored procedures compile execution plans on the server, eliminate network chatter by returning consolidated result sets, and decouple business logic from application code."))

    # ==========================================
    # SLIDE 10: Advanced DBMS: Relational Views
    # ==========================================
    s10 = prs.slides.add_slide(blank_layout)
    set_background(s10)
    add_header(s10, "ADBMS Feature 3", "Analytical Relational Views",
               "Dynamic virtual tables computing institutional analytics and financial arrears in real time.")

    add_card(s10, 0.8, 2.0, 3.6, 4.8, "vw_StudentFeeBalances", [
        "Finance & Billing View",
        "Aggregates course tuition against sum of payment receipts per student.",
        "Calculates net arrears: COALESCE(tuition, 0) - COALESCE(SUM(paid), 0).",
        "Labels payment status: Fully Paid, Partial Payment, Outstanding.",
        "Powers real-time fee balance queries without table bloat."
    ], ACCENT_GREEN if 'ACCENT_GREEN' in locals() else ACCENT_EMERALD)

    add_card(s10, 4.8, 2.0, 3.6, 4.8, "vw_StudentExamResults", [
        "Academic Transcript View",
        "Joins student bio-data, course codes, unit names, marks, and grades.",
        "Applies institutional grading scale automatically via CASE statements.",
        "Provides official transcript summaries for graduation audits.",
        "Eliminates complex repeated JOINs across queries."
    ], ACCENT_CYAN)

    add_card(s10, 8.8, 2.0, 3.6, 4.8, "vw_CourseEnrollmentSummary", [
        "Executive Dashboard View",
        "Aggregates total student registrations by course and department.",
        "Computes enrollment demand and capacity statistics.",
        "Enables university administration to allocate lecture halls and faculty resources dynamically."
    ], ACCENT_PURPLE)

    set_speaker_notes(s10,
        "Discuss views as security and abstraction layers. Emphasize that vw_StudentFeeBalances computes real-time fee arrears without storing redundant computed columns in physical tables.",
        ("Do relational views consume additional disk space?",
         "Standard relational views store only the SQL query definition, not physical row copies. They execute on demand, consuming zero extra disk storage while enforcing security."))

    # ==========================================
    # SLIDE 11: B-Tree Indexing & EXPLAIN Plans
    # ==========================================
    s11 = prs.slides.add_slide(blank_layout)
    set_background(s11)
    add_header(s11, "ADBMS Feature 4", "Query Optimization & B-Tree Indexing",
               "Transforming linear table scans O(n) into logarithmic lookups O(log n) using composite B-Tree indexes.")

    add_card(s11, 0.8, 2.0, 5.6, 4.8, "Composite B-Tree Indexes Created", [
        "idx_students_course_status on students(course_id, status): Accelerates student filtering by department and enrollment state.",
        "idx_registrations_student_sem on registrations(student_id, academic_year, semester): Optimizes semester enrollment checks.",
        "idx_fees_student_date on fee_payments(student_id, payment_date): Speeds up ledger aggregation for real-time balance calculations.",
        "B-Tree index structure maintains balanced depth, ensuring predictable search times even as student records scale to tens of thousands."
    ], ACCENT_BLUE)

    add_card(s11, 6.8, 2.0, 5.6, 4.8, "EXPLAIN Query Plan Benchmark", [
        "Execution Plan Without Index:",
        "  • Scan Type: ALL (Full Table Scan)",
        "  • Complexity: O(n) linear search",
        "  • Rows Inspected: Scans every row in table",
        "",
        "Execution Plan With Composite Index:",
        "  • Scan Type: ref / range",
        "  • Complexity: O(log n) logarithmic search",
        "  • Key Used: idx_students_course_status",
        "  • Rows Inspected: Scans only matching leaf nodes (1-3 rows)"
    ], ACCENT_EMERALD)

    set_speaker_notes(s11,
        "Walk through the query optimization slide. Explain that running EXPLAIN before a query shows the query optimizer using our composite index instead of doing a full table scan.",
        ("When should you NOT add an index to a database column?",
         "Indexes speed up reads but slow down writes (INSERT, UPDATE, DELETE) because the B-Tree must be updated on every write. Columns with low cardinality or high write volumes should not be indexed."))

    # ==========================================
    # SLIDE 12: Security & Role-Based Access (RBAC)
    # ==========================================
    s12 = prs.slides.add_slide(blank_layout)
    set_background(s12)
    add_header(s12, "ADBMS Feature 5", "Database Security & Role-Based Access Control",
               "Enforcing the Principle of Least Privilege directly at the MariaDB grant table level.")

    add_card(s12, 0.8, 2.0, 5.6, 4.8, "Role 1: admin_role", [
        "Full Administrative & Schema Authority.",
        "Privileges: ALL PRIVILEGES on university_sims.*.",
        "Capabilities:",
        "  • DDL Execution: CREATE, ALTER, DROP tables.",
        "  • Full DML: INSERT, UPDATE, DELETE on all entities.",
        "  • User management and role assignment.",
        "  • Financial ledger reconciliation and master backups."
    ], ACCENT_ROSE)

    add_card(s12, 6.8, 2.0, 5.6, 4.8, "Role 2: lecturer_role", [
        "Restricted Academic Grading Authority.",
        "Privileges:",
        "  • SELECT on students, courses, departments, registrations.",
        "  • SELECT, INSERT, UPDATE strictly on examinations.",
        "Security Restrictions:",
        "  • NO ACCESS to fee_payments (Strict financial isolation).",
        "  • NO DDL privileges (Cannot alter schemas or drop tables).",
        "  • Cannot modify student enrollment status."
    ], ACCENT_CYAN)

    set_speaker_notes(s12,
        "Explain RBAC security: We defined admin_role and lecturer_role at the MariaDB level. Lecturers cannot access fee tables or drop schemas, enforcing data security.",
        ("What happens if a lecturer attempts to update a tuition fee in SQL?",
         "MariaDB halts the query immediately and returns an Access Denied error, because lecturer_role lacks UPDATE privileges on the courses table."))

    # ==========================================
    # SLIDE 13: Backup & Disaster Recovery Architecture
    # ==========================================
    s13 = prs.slides.add_slide(blank_layout)
    set_background(s13)
    add_header(s13, "Disaster Recovery", "Automated Backup & Disaster Recovery Pipeline",
               "Automated hot backups and restore verification script (04_backup_recovery.sh) ensuring zero institutional data loss.")

    add_card(s13, 0.8, 2.0, 3.6, 4.8, "Phase 1: Hot Snapshot Dump", [
        "Executes mysqldump with non-blocking transactions.",
        "Flags: --single-transaction --triggers --databases.",
        "Leverages InnoDB Multi-Version Concurrency Control (MVCC).",
        "Zero table locking during operational hours.",
        "Preserves all triggers and schema structures."
    ], ACCENT_BLUE)

    add_card(s13, 4.8, 2.0, 3.6, 4.8, "Phase 2: Archive & Retention", [
        "Timestamped naming: uni_backup_YYYYMMDD_HHMMSS.sql.",
        "Stored in secured backups/ directory.",
        "Integrated integrity verification using SHA-256.",
        "Automated backup rotation and disk quota management."
    ], ACCENT_AMBER)

    add_card(s13, 8.8, 2.0, 3.6, 4.8, "Phase 3: Restore Verification", [
        "Automated recovery testing to uni_sims_recovery_test database.",
        "Compares row counts and table integrity against live database.",
        "Tests disaster recovery without risking production records.",
        "100% verification success confirmed across all 7 tables."
    ], ACCENT_EMERALD)

    set_speaker_notes(s13,
        "Describe the backup and disaster recovery pipeline in 04_backup_recovery.sh. Point out that the script doesn't just create backups—it automatically restores them to a temporary database to verify data integrity.",
        ("How does mysqldump create a backup without locking the database?",
         "By passing --single-transaction, InnoDB creates an MVCC snapshot point. Read transactions read this snapshot while incoming writes continue uninterrupted."))

    # ==========================================
    # SLIDE 14: Interactive ADBMS Lab Walkthrough
    # ==========================================
    s14 = prs.slides.add_slide(blank_layout)
    set_background(s14)
    add_header(s14, "Live Demonstration", "Interactive ADBMS Demonstration Portal",
               "A dedicated live testing environment (advanced_demo.php) designed for defense audits and technical verification.")

    add_card(s14, 0.8, 2.0, 5.6, 4.8, "Live Test Suites Available in the Lab", [
        "1. Trigger Validation Test Suite: Input out-of-bounds marks (-15 or 120) and observe the real-time MariaDB exception catch.",
        "2. Stored Procedure Executer: Interactively execute sp_GetStudentDetails or sp_RecordFeePayment with live parameter inputs.",
        "3. Analytical View Inspector: Real-time queries on vw_StudentFeeBalances showing instant arrears computation.",
        "4. EXPLAIN Query Plan Benchmarker: Click to run EXPLAIN and visualize index key lookups and scan rows."
    ], ACCENT_CYAN)

    add_card(s14, 6.8, 2.0, 5.6, 4.8, "Live Demonstration Protocol", [
        "Step 1: Open http://localhost/university_sims/public/index.php to show the system dashboard and KPI counters.",
        "Step 2: Navigate to advanced_demo.php to test the trigger with an invalid exam score of 120.",
        "Step 3: Show the caught exception: 'Exam marks must be between 0 and 100'.",
        "Step 4: Record a payment of KSh 40,000 and show vw_StudentFeeBalances updating immediately.",
        "Step 5: Open phpMyAdmin to show the change synchronized live."
    ], ACCENT_EMERALD)

    set_speaker_notes(s14,
        "Introduce the live demonstration. Offer to run the ADBMS Lab live to show triggers, stored procedures, and EXPLAIN plans executing on MariaDB in real time.",
        ("Can we see what happens when an invalid mark is entered?",
         "Open advanced_demo.php, enter 125 in the Trigger Test form, and click Test. The panel will see the MariaDB SQLSTATE 45000 exception displayed on screen."))

    # ==========================================
    # SLIDE 15: Team Member Presentation Matrix
    # ==========================================
    s15 = prs.slides.add_slide(blank_layout)
    set_background(s15)
    add_header(s15, "Team Defense Strategy", "Team Member Speaking Roles & Defense Matrix",
               "Structured allocation of slides and technical topics to ensure every team member presents with confidence.")

    add_card(s15, 0.8, 2.0, 2.7, 4.8, "Speaker 1: Team Lead", [
        "Assigned Slides: 1 - 3",
        "Core Topics:",
        "  • Institutional context.",
        "  • Problem statement.",
        "  • 3-Tier architecture.",
        "Defense Role: Handles high-level architecture and scope questions."
    ], ACCENT_BLUE)

    add_card(s15, 3.8, 2.0, 2.7, 4.8, "Speaker 2: DB Specialist", [
        "Assigned Slides: 4 - 6",
        "Core Topics:",
        "  • 3NF Normalization proof.",
        "  • 7 Core relational entities.",
        "  • Departments and fee structure.",
        "Defense Role: Handles normalization, foreign keys, and ERD questions."
    ], ACCENT_CYAN)

    add_card(s15, 6.8, 2.0, 2.7, 4.8, "Speaker 3: ADBMS Engine", [
        "Assigned Slides: 7 - 10",
        "Core Topics:",
        "  • 5 Functional modules.",
        "  • Trigger boundary logic.",
        "  • Stored procedures & Views.",
        "Defense Role: Handles triggers, procedures, and SQL logic questions."
    ], ACCENT_PURPLE)

    add_card(s15, 9.8, 2.0, 2.7, 4.8, "Speaker 4: Ops & Demo", [
        "Assigned Slides: 11 - 14",
        "Core Topics:",
        "  • B-Tree indexing & EXPLAIN.",
        "  • RBAC security roles.",
        "  • Backup & disaster recovery.",
        "  • Live ADBMS Lab walkthrough.",
        "Defense Role: Conducts the live demo and explains query plans."
    ], ACCENT_EMERALD)

    set_speaker_notes(s15,
        "Briefly demonstrate team cohesion and division of labor. Each member has a clear area of ownership and is prepared to answer targeted questions from the panel.",
        ("How did your team divide the workload?",
         "We structured roles along the software lifecycle: requirements and normalization, database engine logic, full-stack application development, and performance optimization/testing."))

    # ==========================================
    # SLIDE 16: Conclusion & Impact
    # ==========================================
    s16 = prs.slides.add_slide(blank_layout)
    set_background(s16)

    # Decorative bar
    bar16 = s16.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.8), Inches(0.15), Inches(3.4))
    bar16.fill.solid()
    bar16.fill.fore_color.rgb = ACCENT_EMERALD
    bar16.line.fill.background()

    t_box16 = s16.shapes.add_textbox(Inches(1.2), Inches(1.7), Inches(11.2), Inches(3.4))
    tf16 = t_box16.text_frame
    tf16.word_wrap = True

    p_tag16 = tf16.paragraphs[0]
    p_tag16.text = "SUMMARY & CONCLUSION"
    p_tag16.font.size = Pt(12)
    p_tag16.font.bold = True
    p_tag16.font.color.rgb = ACCENT_CYAN
    p_tag16.space_after = Pt(10)

    p_main16 = tf16.add_paragraph()
    p_main16.text = "UniSIMS: Engineered for Academic Integrity & Enterprise Resilience"
    p_main16.font.size = Pt(26)
    p_main16.font.bold = True
    p_main16.font.color.rgb = TEXT_WHITE
    p_main16.space_after = Pt(14)

    p_desc16 = tf16.add_paragraph()
    p_desc16.text = "A production-grade student information management system bridging rigorous relational theory (3NF) and advanced database engine capabilities on MariaDB."
    p_desc16.font.size = Pt(14)
    p_desc16.font.color.rgb = TEXT_MUTED
    p_desc16.space_after = Pt(20)

    add_card(s16, 0.8, 5.3, 3.6, 1.6, "100% 3NF Normalized", ["Zero insertion, update, or deletion anomalies across 7 core entities."], ACCENT_BLUE)
    add_card(s16, 4.8, 5.3, 3.6, 1.6, "Engine-Level Guardrails", ["Boundary triggers, ACID stored procedures, and analytical views."], ACCENT_EMERALD)
    add_card(s16, 8.8, 5.3, 3.6, 1.6, "Production Resilient", ["Automated hot backup, B-Tree indexing, and RBAC security."], ACCENT_AMBER)

    set_speaker_notes(s16,
        "Summarize the final impact: 3NF compliance, engine-enforced integrity, real-time synchronization, and disaster recovery. Thank the panel and invite questions.",
        ("What is the single biggest strength of this project?",
         "The strict enforcement of institutional integrity directly inside the database engine using 3NF normalization, active triggers, and ACID-compliant stored procedures."))

    output_path = "/home/ktec/Projects/university_sims/docs/UniSIMS_Presentation.pptx"
    prs.save(output_path)
    print(f"Presentation saved successfully to: {output_path}")

if __name__ == "__main__":
    create_presentation()
