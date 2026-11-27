# UniSIMS: Capstone Presentation & Defense Master Guide

> **Prepared for**: Kennedy Kalawa & Capstone Project Defense Team  
> **Course**: Advanced Database Management Systems (ADBMS)  
> **System**: University Student Information Management System (UniSIMS)  
> **Presentation Artifacts**:
> - Interactive Web Slides: [`docs/presentation.html`](file:///home/ktec/Projects/university_sims/docs/presentation.html)
> - PowerPoint Slide Deck: [`docs/UniSIMS_Presentation.pptx`](file:///home/ktec/Projects/university_sims/docs/UniSIMS_Presentation.pptx)
> - Academic Report: [`docs/PROJECT_REPORT.docx`](file:///home/ktec/Projects/university_sims/docs/PROJECT_REPORT.docx) & [`docs/PROJECT_REPORT.md`](file:///home/ktec/Projects/university_sims/docs/PROJECT_REPORT.md)

---

## 1. Quick Presentation Checklist Before Standing Up

1. **Start Apache & MySQL**: Ensure XAMPP is running (`sudo /opt/lampp/lampp start`).
2. **Open Three Tabs in Chrome / Firefox**:
   - **Tab 1 (Slides)**: `file:///home/ktec/Projects/university_sims/docs/presentation.html` (Press `F` for fullscreen, `S` for speaker notes).
   - **Tab 2 (System Dashboard)**: `http://localhost/university_sims/public/index.php`.
   - **Tab 3 (ADBMS Lab)**: `http://localhost/university_sims/public/advanced_demo.php`.
   - **Tab 4 (phpMyAdmin)**: `http://localhost/phpmyadmin/` (to prove real-time two-way synchronization).
3. **Know Your Assigned Role**: Look at the speaker allocation table below.

---

## 2. Team Member Role Allocation & Slide Breakdown

| Role | Assigned Speaker | Slides Owned | Core Topics Covered |
| :--- | :--- | :--- | :--- |
| **Speaker 1: Team Lead** | Team Lead | **Slides 1 – 3** | Introduction, Institutional Problem Statement, 3-Tier Architecture. |
| **Speaker 2: Database Architect** | DB Specialist | **Slides 4 – 6** | 3NF Normalization Proof ($0\text{NF} \to 3\text{NF}$), 7 Relational Tables, 5 Departments & Fee Schedules. |
| **Speaker 3: ADBMS Engine Specialist** | ADBMS Lead | **Slides 7 – 10** | 5 Functional Modules, Database Triggers ($0 \le \text{mark} \le 100$), Stored Procedures, Analytical Views. |
| **Speaker 4: Operations & Live Demo** | Systems / QA Lead | **Slides 11 – 16** | B-Tree Indexing & `EXPLAIN`, RBAC Security, Backup & Disaster Recovery, Live ADBMS Lab Demo & Conclusion. |

---

## 3. Slide-by-Slide Speaking Script

### Speaker 1: Introduction, Problem Statement & Architecture

#### Slide 1: Title Slide (UniSIMS Overview)
> *"Good morning / afternoon members of the panel. Our project is UniSIMS — the University Student Information Management System.  
> While many student management systems simply store web form inputs, UniSIMS was engineered around **strict 3NF database normalization** and **advanced database engine features** in MariaDB.  
> We have implemented active triggers, ACID-compliant stored procedures, pre-aggregated relational views, composite B-Tree indexing, and role-based access control directly within the database engine to guarantee absolute academic and financial integrity."*

#### Slide 2: Problem Statement & Objectives
> *"In many higher educational institutions, administrative departments operate in silos.  
> We identified four major vulnerabilities in traditional legacy setups:  
> First, **data redundancy**, where student records are duplicated across faculty spreadsheets, leading to update and deletion anomalies.  
> Second, **vulnerability to grade tampering**, where marks are recorded without engine-level boundary checks.  
> Third, **fee reconciliation lag**, where academic registrars have no instant visibility into fee arrears, causing delays in exam clearance.  
> UniSIMS resolves all of these by decomposing the schema into exactly 7 normalized tables in 3NF and moving critical business validation directly into the MariaDB kernel."*

#### Slide 3: 3-Tier Enterprise Architecture
> *"UniSIMS is built on a decoupled 3-tier enterprise architecture:  
> **Tier 1 (Presentation)** is a clean, responsive web dashboard built with HTML5 and Tailwind CSS, utilizing zero external image dependencies by embedding native SVG vector icons.  
> **Tier 2 (Application Data Layer)** is built on PHP 8.2 using PDO. Our universal connector automatically detects Unix sockets and TCP ports, and enforces 100% prepared statements with parameterized inputs, eliminating SQL injection.  
> **Tier 3 (Database Engine)** is MariaDB with the InnoDB storage engine, which provides full ACID compliance, active triggers, stored procedures, and composite B-tree indexing."*

---

### Speaker 2: Database Normalization, Schema & Curriculum

#### Slide 4: Database Normalization Journey (0NF $\to$ 3NF)
> *"Database normalization was the foundational phase of our design.  
> We started with an unnormalized **0NF** structure containing repeating course groups and multi-valued exam marks, which suffered from severe insertion and deletion anomalies.  
> In **1NF**, we flattened the structure so every attribute contains strictly atomic, indivisible scalar values.  
> In **2NF**, we eliminated all partial functional dependencies on composite keys.  
> In **3NF**, we eliminated all **transitive dependencies**. For example, in an unnormalized record, `student_id` determined `course_id`, which determined `department_name`. That was a transitive dependency ($X \to Y \to Z$). We resolved it by isolating departments and courses into separate relational entities linked exclusively by Foreign Keys."*

#### Slide 5: The 7 Core Database Tables & ERD
> *"Our database consists of exactly 7 core relational entities:  
> 1. `departments`: Houses faculty hierarchies and office assignments.  
> 2. `courses`: Stores degree programs and semester fee rates in KSh.  
> 3. `instructors`: Stores academic staff details.  
> 4. `students`: Tracks bio-data and standardized admission numbers like `CT100/G/24900/24`.  
> 5. `registrations`: Acts as the bridge entity connecting students to courses per academic year and semester.  
> 6. `examinations`: Stores validated continuous assessment and exam scores.  
> 7. `fee_payments`: Our financial ledger recording payment amounts, methods, and unique receipt serials."*

#### Slide 6: Departments & Differential Tuition Schedule
> *"As requested by university management, our curriculum is mapped across **5 distinct departments** and **12 specialized degree programs**:  
> - **Computing**: Offers Information Technology, Computer Science, Software Engineering, and Computer Systems Engineering at **KSh 80,000** per semester.  
> - **Engineering**: Offers Computer Systems Engineering, Mechanical, and Telecommunications at **KSh 80,000**.  
> - **Business**: Offers BIT, Economics, and Finance at **KSh 60,000**.  
> - **Education**: Offers Education Science and Education Arts at **KSh 60,000**.  
> - **Medicine**: Offers Nursing, Clinical Medicine, and Pure Medicine (MBChB) at **KSh 100,000**.  
> All monetary values are strictly typed as `DECIMAL(10,2)` in Kenyan Shillings (KSh) to eliminate floating-point rounding errors."*

---

### Speaker 3: Functional Modules, Triggers, Procedures & Views

#### Slide 7: Five Core Academic Modules
> *"UniSIMS organizes daily operations into 5 tightly coupled modules:  
> Module 1 handles **Student Admissions and Bio-Data**.  
> Module 2 manages the **Course Catalog and Curriculum Mapping**.  
> Module 3 automates **Semester Course Registrations** and prevents duplicate enrollments.  
> Module 4 handles **Examination Grading** using standard academic grade boundaries (A to F).  
> Module 5 operates the **Fee Payment Ledger**, tracking payments against billed tuition.  
> Crucially, every page queries MariaDB directly. Any record created or altered in phpMyAdmin reflects in the user interface immediately upon refresh."*

#### Slide 8: Database Triggers (Active Kernel Integrity)
> *"To ensure that data integrity cannot be bypassed, we implemented active triggers directly on the database:  
> Our primary trigger is `trg_CheckExamMarksBoundary`. It executes `BEFORE INSERT` and `BEFORE UPDATE` on the `examinations` table.  
> If an examiner or script enters a score less than 0 or greater than 100, the trigger calls `SIGNAL SQLSTATE '45000'` with a custom exception message. This aborts the operation at the database engine level and rolls back the transaction.  
> Our second trigger, `trg_UpdateRegistrationStatus`, executes `AFTER INSERT` on examinations to automatically flip registration status to `'completed'` when grades are posted."*

#### Slide 9: Parameterized Stored Procedures
> *"We implemented three pre-compiled stored procedures to encapsulate complex transactional workflows:  
> 1. `sp_GetStudentDetails(student_id)`: Executes an optimized multi-table JOIN across students, courses, and departments, retrieving complete academic profiles in a single network round-trip.  
> 2. `sp_RegisterStudentCourse(...)`: Validates semester eligibility, verifies active student standing, and inserts registration records atomically.  
> 3. `sp_RecordFeePayment(...)`: Wraps payment insertion and ledger accounting in an ACID transaction, rolling back automatically if receipt numbering conflicts occur."*

#### Slide 10: Analytical Relational Views
> *"Rather than cluttering physical tables with calculated columns, we designed pre-aggregated relational views:  
> Our flagship view is `vw_StudentFeeBalances`. It dynamically computes:  
> `COALESCE(c.tuition_fee, 0) - COALESCE(SUM(p.amount_paid), 0) AS balance_due`.  
> This calculates real-time fee arrears without storing duplicate totals.  
> Similarly, `vw_StudentExamResults` compiles student transcripts and letter grades, and `vw_CourseEnrollmentSummary` provides real-time enrollment statistics for institutional planning."*

---

### Speaker 4: Optimization, Security, Disaster Recovery & Live Demo

#### Slide 11: B-Tree Indexing & Query Optimization (`EXPLAIN`)
> *"To maintain high performance as the student population scales, we implemented composite B-Tree indexing.  
> For example, `idx_students_course_status` indexes `(course_id, status)`.  
> When filtering students by course and status, MariaDB no longer performs a linear table scan of $O(n)$ where every row is inspected.  
> Instead, it performs a logarithmic B-Tree traversal of $O(\log n)$, dropping scanned rows to 1 to 3 records.  
> When tested with `EXPLAIN`, the query plan clearly reports `type: ref` and lists our index under `key`, proving the engine uses the index."*

#### Slide 12: Database Security & Role-Based Access Control (RBAC)
> *"We enforced the Principle of Least Privilege directly at the MariaDB level using two dedicated roles:  
> `admin_role` has full administrative and DDL permissions (`ALL PRIVILEGES`) to configure schemas, manage accounts, and process fee ledgers.  
> `lecturer_role` is strictly scoped: it has `SELECT` permissions on students and courses, and `SELECT, INSERT, UPDATE` on examinations.  
> Lecturers have **no access** to fee payment tables and **no DDL privileges**. Even if a lecturer account is compromised, the database engine will block any attempt to alter schemas or delete tables."*

#### Slide 13: Backup & Disaster Recovery Architecture
> *"For institutional business continuity, we developed `04_backup_recovery.sh`.  
> It performs non-blocking hot backups using `mysqldump` with `--single-transaction`. By leveraging InnoDB's Multi-Version Concurrency Control (MVCC), it captures a consistent point-in-time snapshot without locking tables or interrupting active users.  
> Furthermore, the script automatically restores the snapshot into a temporary database (`uni_sims_recovery_test`) and compares row counts across all 7 tables to verify 100% data fidelity."*

#### Slide 14: Live Demonstration Protocol (The ADBMS Lab)
> *"We will now walk you through our interactive ADBMS Demonstration Portal at `advanced_demo.php`, which was built specifically for this evaluation."*  
> *(Proceed to the Live Demo steps below)*.

#### Slide 16: Conclusion
> *"In conclusion, UniSIMS demonstrates that moving integrity constraints, procedures, and security into the database engine creates a resilient, high-performance system.  
> All 7 tables comply with 3NF, triggers guard against illegal marks, stored procedures provide atomic transactions, and B-Tree indexing guarantees scalability.  
> Thank you, and we now welcome questions from the panel."*

---

## 4. Live Demonstration Protocol (Under 3 Minutes)

When the panel says *"Show us the system running"*, follow this exact 4-step sequence:

### Step 1: Show the Overview Dashboard (30 Seconds)
- Go to: `http://localhost/university_sims/public/index.php`.
- Point out the KPI statistics: **15 Students**, **12 Courses**, **5 Departments**, **KSh 300,000 Collected**.
- Point out the clean UI, zero emojis, and Lucide vector icons.

### Step 2: Demonstrate the Exam Mark Trigger Interception (45 Seconds)
- Click **ADBMS Lab** in the top navigation bar (`advanced_demo.php`).
- Scroll down to the **"Trigger Validation Test"** card.
- Enter an invalid score: **`125`** (or **`-15`**).
- Click **"Test Trigger Validation"**.
- **Result**: The page catches the MariaDB `SQLSTATE 45000` exception in a red card:  
  *`"Trigger Error: Exam marks must be between 0 and 100"`*.
- Explain to the panel: *"This proves that even if an application bug attempts to insert invalid data, MariaDB's BEFORE INSERT trigger blocks the commit at the engine level."*

### Step 3: Demonstrate Stored Procedure & Real-Time Sync (45 Seconds)
- In the ADBMS Lab, under **"Stored Procedure: Student Details"**, select Student ID `1` (`Theodore Kariuki`).
- Click **"Execute Procedure"**.
- Show that it returns his course (`Computer Science`), department (`Computing`), and tuition rate (`KSh 80,000`).
- Now click **Fee Management** in the navbar (`fees.php`).
- Record a payment of **`KSh 40,000`** via Bank for Theodore Kariuki.
- Show how `vw_StudentFeeBalances` immediately updates his arrears from `KSh 80,000` down to `KSh 40,000`.

### Step 4: Prove Query Optimization (`EXPLAIN`) (30 Seconds)
- In the ADBMS Lab, scroll to **"B-Tree Indexing & Query Plan"**.
- Click **"Run EXPLAIN Plan"**.
- Point to the execution table:
  - `type`: **`ref`** (not `ALL`).
  - `key`: **`idx_students_course_status`**.
  - `rows`: **`1 - 3`**.
- Explain: *"This confirms that the MariaDB optimizer uses our B-Tree composite index rather than scanning the entire table."*

---

## 5. Top 15 Examiner Defense Questions & Winning Answers

### Category A: Database Theory & Normalization

#### Q1: "Why did you stop at 3NF? Why not BCNF or 4NF?"
**Model Answer**:  
*"In our schema, every determinant is a candidate key. There are no non-trivial functional dependencies where $X \to Y$ and $X$ is not a superkey, which means our schema naturally satisfies Boyce-Codd Normal Form (BCNF) as well. Furthermore, our entities contain no multi-valued dependencies (which 4NF addresses), because course registration and examination marks are decomposed into distinct associative tables."*

#### Q2: "Can you prove that your schema has zero transitive dependencies?"
**Model Answer**:  
*"Yes. A transitive dependency occurs when non-key attribute $Z$ depends on non-key attribute $Y$, which depends on primary key $X$ ($X \to Y \to Z$).  
In our design:
- In `students`, every attribute (`name`, `email`, `year_of_study`) depends strictly on `student_id`.
- Department details (`department_name`, `office_location`) are stored only in `departments`, not in `students` or `courses`.
- Therefore, no non-key attribute depends on another non-key attribute."*

#### Q3: "Why did you create an associative entity (`registrations`) instead of linking students directly to courses?"
**Model Answer**:  
*"Students and courses have a Many-to-Many ($M:N$) relationship: one student registers for multiple courses across different semesters, and one course enrolls multiple students. A direct foreign key in either table would violate 1NF by creating repeating groups. The associative entity `registrations` resolves this into two 1:Many relationships (`students` 1:M `registrations` and `courses` 1:M `registrations`), while allowing us to track semester, academic year, and enrollment status."*

---

### Category B: Advanced SQL, Triggers & Stored Procedures

#### Q4: "What is the difference between a BEFORE INSERT and an AFTER INSERT trigger?"
**Model Answer**:  
*"A `BEFORE INSERT` trigger executes prior to writing data to the transaction log or disk. It has write access to the `NEW` virtual row, allowing it to validate constraints and abort the operation using `SIGNAL SQLSTATE` before any disk persistence occurs. An `AFTER INSERT` trigger executes once the new row is already committed in the transaction; it is ideal for propagating cascading updates to other tables, such as our `trg_UpdateRegistrationStatus` which updates registration status after exam marks are written."*

#### Q5: "Why did you use stored procedures instead of putting all SQL in PHP?"
**Model Answer**:  
*"Three main advantages:  
1. **Network Performance**: Stored procedures run on the database server. Instead of sending multiple SQL queries back and forth across the network, we execute a single `CALL sp_Name()` command.  
2. **Pre-compiled Execution Plans**: MariaDB parses and compiles stored procedures once in server memory, speeding up repeated executions.  
3. **Decoupled Security**: We can grant users permission to execute a stored procedure without granting them direct `SELECT` or `UPDATE` privileges on the underlying raw tables."*

#### Q6: "How do stored procedures maintain ACID properties during financial transactions?"
**Model Answer**:  
*"In `sp_RecordFeePayment`, we wrap the payment insertion inside `START TRANSACTION;` and `COMMIT;`. If an error occurs (such as a duplicate receipt number or missing student key), MariaDB intercepts the condition and executes `ROLLBACK;`, ensuring that ledgers are never left in a partial or corrupt state."*

---

### Category C: Views, Calculations & Data Types

#### Q7: "Does `vw_StudentFeeBalances` duplicate table data on the hard drive?"
**Model Answer**:  
*"No. In MariaDB, a standard view is a virtual table defined as a stored SQL query. It does not store physical rows on disk. When `SELECT * FROM vw_StudentFeeBalances` is executed, the query planner merges the view definition with the query and computes arrears dynamically. This provides data abstraction and security without consuming additional storage."*

#### Q8: "Why did you use `DECIMAL(10,2)` instead of `FLOAT` or `DOUBLE` for tuition fees and payments?"
**Model Answer**:  
*"Binary floating-point types (`FLOAT` and `DOUBLE`) store approximations based on IEEE 754 standards, which can introduce subtle rounding inaccuracies (e.g., $0.1 + 0.2 = 0.30000000000000004$). For financial ledgers where every cent must balance, `DECIMAL(10,2)` stores numbers as exact fixed-point decimals, guaranteeing mathematical precision."*

---

### Category D: Query Optimization & Indexing

#### Q9: "Explain how your B-Tree composite index works."
**Model Answer**:  
*"We created `idx_students_course_status` on `students(course_id, status)`.  
A B-Tree index organizes keys in a sorted, balanced tree structure. The composite index sorts first by `course_id`, and then by `status` within each course.  
When a query executes `WHERE course_id = 2 AND status = 'active'`, the database traverses the B-Tree directly to the matching leaf node in $O(\log n)$ time, completely avoiding a full table scan."*

#### Q10: "What does the `EXPLAIN` keyword tell you about a query?"
**Model Answer**:  
*"The `EXPLAIN` keyword exposes the MariaDB query optimizer's execution plan. Key columns include:
- `type`: Shows scan type (`ALL` means full table scan; `ref` or `range` means index-assisted lookup).
- `possible_keys`: Lists all indexes available for the query.
- `key`: Shows the actual index chosen by the optimizer.
- `rows`: Estimates the number of rows scanned to produce the result set."*

---

### Category E: Security, Operations & Architecture

#### Q11: "How does your PHP code prevent SQL injection?"
**Model Answer**:  
*"Every database query in UniSIMS is executed using PDO prepared statements with parameter binding:
```php
$stmt = $pdo->prepare('SELECT * FROM students WHERE reg_number = ?');
$stmt->execute([$reg_number]);
```
Prepared statements separate SQL syntax from user data. The database compiles the SQL structure first, treating user input strictly as literal values rather than executable code."*

#### Q12: "How does Role-Based Access Control (RBAC) work in your project?"
**Model Answer**:  
*"We created dedicated database roles in SQL: `admin_role` and `lecturer_role`.  
We grant `admin_role` full `ALL PRIVILEGES` on the database. For `lecturer_role`, we grant `SELECT` on courses, students, and registrations, and `SELECT, INSERT, UPDATE` strictly on `examinations`. Lecturers have zero access to `fee_payments` and no DDL permissions, enforcing the Principle of Least Privilege."*

#### Q13: "How does your backup script prevent table locking?"
**Model Answer**:  
*"In `04_backup_recovery.sh`, `mysqldump` runs with `--single-transaction`. Because all 7 tables use the InnoDB storage engine, MariaDB creates an MVCC (Multi-Version Concurrency Control) snapshot. Read queries read from this consistent snapshot while concurrent client transactions continue writing to the tables without experiencing downtime."*

#### Q14: "What happens if an examiner deletes a student from the system?"
**Model Answer**:  
*"Our foreign key definitions on `registrations` and `examinations` specify `ON DELETE CASCADE`. If a student record is removed, all dependent semester registrations and exam grades are automatically cleaned up, preventing orphan records. However, `fee_payments` records are protected to maintain an audit trail according to financial compliance standards."*

#### Q15: "How does your system guarantee that the UI and database are in real-time sync?"
**Model Answer**:  
*"UniSIMS does not use static JSON files or temporary caching layers. Every request to a page (e.g. `students.php` or `fees.php`) triggers a live PDO query against the active MariaDB database. If a record is inserted or updated in phpMyAdmin, refreshing the web page immediately reflects the change."*

---

## 6. How to Handle Tough or Curveball Examiner Questions

1. **If an examiner asks to see the code of a trigger**:
   - Open `sql/03_advanced_concepts.sql` or show the ADBMS Lab screen (`advanced_demo.php`).
   - Point out `CREATE TRIGGER trg_CheckExamMarksBoundary BEFORE INSERT ON examinations`.

2. **If an examiner asks: "Can you change something in phpMyAdmin and show it on the site?"**:
   - Open `http://localhost/phpmyadmin/` in a new tab.
   - Go to `university_sims` $\to$ `students`. Edit any student's phone number or name.
   - Switch back to `http://localhost/university_sims/public/students.php` and press `F5`. The updated value appears instantly.

3. **If you don't know the exact answer to an unexpected theoretical question**:
   - Stay calm. Frame your answer around relational integrity and ACID principles:
     > *"In our design, we prioritized database engine-level integrity using 3NF normalization, foreign key cascading constraints, and ACID transactions in InnoDB to ensure that no operational anomalies can occur."*

Good luck with your capstone defense! You have a complete, mathematically normalized, and thoroughly tested system. Present with pride and confidence!
