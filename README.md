# University Student Information Management System (UniSIMS)

An enterprise-grade relational database solution engineered to streamline university operations: student enrollment, course administration, course registration logistics, examination evaluations, and fee ledger management.

Built for **Advanced Database Management Systems (ADBMS)** using **MySQL/MariaDB**, **PHP**, and **XAMPP / phpMyAdmin**.

---

## Key Features

1. **Third Normal Form (3NF) Relational Architecture**: Exactly 7 normalized tables eliminating insert, update, and deletion anomalies.
2. **Five Academic Departments & Differential Tuition**:
   * **Computing**: KSh 80,000.00
   * **Engineering**: KSh 80,000.00
   * **Business**: KSh 60,000.00
   * **Education**: KSh 60,000.00
   * **Medicine**: KSh 100,000.00
3. **Twelve Specialized Degree Courses**: Information Technology, Computer Science, Software Engineering, Computer Systems Engineering, Nursing, Clinical Medicine, Pure Medicine, Education Science, Education Arts, Business Information Technology, Economics, Finance.
4. **Database-Level Business Rules & Triggers**:
   * Boundary constraint trigger ($0 \le \text{mark} \le 100$) on `exam_results`.
   * Automatic course registration completion status trigger upon recording grades.
5. **Procedural Logic & Abstraction**:
   * Stored procedures (`sp_GetStudentDetails`, `sp_RegisterStudentCourse`, `sp_RecordFeePayment`).
   * Relational views (`vw_StudentFeeBalances`, `vw_StudentExamResults`, `vw_CourseEnrollmentSummary`).
6. **Physical Optimization & Security**:
   * B-Tree composite indexes verified via `EXPLAIN`.
   * Role-Based Access Control (RBAC) separating `admin_role` and `lecturer_role` (least privilege enforcement).
7. **Disaster Recovery**:
   * Automated logical backup generation and simulated point-in-time database restoration (`sql/04_backup_recovery.sh`).
8. **Interactive Web Interface**:
   * Responsive PHP portal styled with modern CSS and Lucide SVG icons (zero emojis).
   * Live interactive laboratory (`advanced_demo.php`) for live trigger, view, EXPLAIN plan, and RBAC testing.
   * Direct real-time two-way synchronization with phpMyAdmin (no manual file exports needed).

---

## Directory Structure

```
university_sims/
├── index.php                 # Root router (redirects to public/index.php)
├── link_to_htdocs.sh         # Helper to symlink project to XAMPP htdocs
├── docs/
│   └── PROJECT_REPORT.md     # Full academic report (0NF to 3NF proof, ERD, SQL DDL)
├── sql/
│   ├── 01_schema.sql         # 7 Normalized 3NF tables DDL
│   ├── 02_seed_data.sql      # Seed data (5 depts, 12 courses, students, fees)
│   ├── 03_advanced_concepts.sql # Triggers, procedures, views, indexes, roles
│   ├── 04_backup_recovery.sh # Disaster recovery test script
│   └── university_sims_full_setup.sql # Consolidated master database script
├── backups/                  # Directory for automated SQL database dumps
└── public/                   # Web application root
    ├── db.php                # Universal PDO connector (XAMPP / socket / TCP)
    ├── header.php & footer.php
    ├── icons.php             # Lucide SVG icon renderer
    ├── style.css             # Responsive styling
    ├── index.php             # Operations dashboard
    ├── students.php          # Student management & SP profile view
    ├── courses.php           # Course catalog & enrollment metrics
    ├── registrations.php     # Course enrollments
    ├── examinations.php      # Marks recording & grade calculations
    ├── fees.php              # Fee collection & compliance ledger
    ├── advanced_demo.php     # Interactive ADBMS Lab
    └── test_db.php           # Real-time sync & diagnostics
```

---

## Quick Setup Guide

### 1. Database Setup (phpMyAdmin / XAMPP)
Ensure MySQL is running in your XAMPP Control Panel, then:
* Import `sql/university_sims_full_setup.sql` into phpMyAdmin (`http://localhost/phpmyadmin/`), or run:
  ```bash
  /opt/lampp/bin/mysql -u root -S /opt/lampp/var/mysql/mysql.sock < sql/university_sims_full_setup.sql
  ```

### 2. Link to XAMPP Web Root
Link the repository into XAMPP's `htdocs`:
```bash
sudo ln -s /home/ktec/Projects/university_sims /opt/lampp/htdocs/university_sims
```

### 3. Open in Browser
* **Web Portal**: [http://localhost/university_sims/](http://localhost/university_sims/)
* **ADBMS Lab**: [http://localhost/university_sims/public/advanced_demo.php](http://localhost/university_sims/public/advanced_demo.php)
* **Diagnostics**: [http://localhost/university_sims/public/test_db.php](http://localhost/university_sims/public/test_db.php)
* **phpMyAdmin**: [http://localhost/phpmyadmin/index.php?route=/database/structure&db=university_sims](http://localhost/phpmyadmin/index.php?route=/database/structure&db=university_sims)
