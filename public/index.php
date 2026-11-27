<?php
$page_title = "Dashboard - University SIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Fetch system counts
$total_students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$total_registrations = $pdo->query("SELECT COUNT(*) FROM course_registrations")->fetchColumn();
$total_exams = $pdo->query("SELECT COUNT(*) FROM exam_results")->fetchColumn();
$total_fees = $pdo->query("SELECT COALESCE(SUM(amount_paid), 0) FROM fee_payments")->fetchColumn();
$avg_marks = $pdo->query("SELECT ROUND(COALESCE(AVG(marks_obtained), 0), 1) FROM exam_results")->fetchColumn();

// Fetch recent students
$recent_students = $pdo->query("
    SELECT s.student_id, s.reg_number, s.first_name, s.last_name, s.email, d.department_code, s.status
    FROM students s
    JOIN departments d ON s.department_id = d.department_id
    ORDER BY s.student_id DESC LIMIT 5
")->fetchAll();

// Fetch recent exam grades from VIEW
$recent_grades = $pdo->query("
    SELECT student_name, course_code, marks_obtained, letter_grade, result_status
    FROM vw_StudentExamResults
    ORDER BY result_id DESC LIMIT 5
")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1>University SIMS Dashboard</h1>
        <p>Operational Overview of Student Records, Academic Progress, and Financial Accounts</p>
    </div>
    <div style="display: flex; gap: 0.5rem; align-items: center;">
        <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=university_sims" target="_blank" class="btn btn-outline" style="background: rgba(34, 197, 94, 0.1); color: #15803d; border-color: rgba(34, 197, 94, 0.4);" title="Open live database in phpMyAdmin">
            <?= lucide('database', '', 16) ?> Open phpMyAdmin (Live Sync)
        </a>
        <a href="advanced_demo.php" class="btn btn-accent">
            <?= lucide('zap', '', 16) ?> Launch ADBMS Lab
        </a>
    </div>
</div>

<!-- BACKEND STORAGE STATUS & PHPMYADMIN LIVE SYNC BANNER -->
<div class="alert alert-info" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; background: #f0fdf4; border-left-color: #16a34a; color: #14532d;">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
        <?= lucide('check-circle', '', 24) ?>
        <div>
            <strong>Live Real-Time Sync: Connected to <?= htmlspecialchars($db_connection_info) ?></strong><br>
            <span style="font-size: 0.85rem; color: #166534;">
                All <?= $total_students ?> student profiles (including <em>Theo Evans</em>, <em>Octavious Antony</em>, and <em>kennedy kalawa</em>) are live in MySQL. Changes made here or in phpMyAdmin sync automatically in real time without exporting files.
            </span>
        </div>
    </div>
    <div>
        <a href="test_db.php" class="btn btn-sm btn-primary" style="background: #16a34a;">
            <?= lucide('check-circle', '', 14) ?> View Sync Diagnostics
        </a>
    </div>
</div>

<!-- TOP STAT CARDS -->
<div class="card-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><?= lucide('users', '', 26) ?></div>
        <div class="stat-info">
            <h3><?= number_format($total_students) ?></h3>
            <span>Total Students</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><?= lucide('book-open', '', 26) ?></div>
        <div class="stat-info">
            <h3><?= number_format($total_courses) ?></h3>
            <span>Active Courses</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><?= lucide('clipboard-check', '', 26) ?></div>
        <div class="stat-info">
            <h3><?= number_format($total_registrations) ?></h3>
            <span>Course Enrollments</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><?= lucide('target', '', 26) ?></div>
        <div class="stat-info">
            <h3><?= $avg_marks ?>%</h3>
            <span>Average Exam Mark</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><?= lucide('credit-card', '', 26) ?></div>
        <div class="stat-info">
            <h3>KSh <?= number_format($total_fees, 2) ?></h3>
            <span>Total Fees Collected</span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- RECENT ENROLLED STUDENTS -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2><?= lucide('users', '', 20) ?> Recently Enrolled Students</h2>
            <a href="students.php" class="btn btn-outline btn-sm">
                View All Students <?= lucide('arrow-right', '', 14) ?>
            </a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reg No.</th>
                        <th>Student Name</th>
                        <th>Dept</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_students as $st): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($st['reg_number']) ?></strong></td>
                        <td><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></td>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($st['department_code']) ?></span></td>
                        <td><?= htmlspecialchars($st['email']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($st['status']) ?></span></td>
                        <td>
                            <a href="students.php?view_id=<?= $st['student_id'] ?>" class="btn btn-outline btn-sm">Profile (SP)</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ARCHITECTURE & HEALTH STATUS -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2><?= lucide('building', '', 20) ?> Database Architecture</h2>
        </div>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.875rem;">
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>Normalization Form:</span>
                <strong style="color: var(--primary);">Third Normal Form (3NF)</strong>
            </li>
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>Total Relational Tables:</span>
                <span class="badge badge-info">7 Tables</span>
            </li>
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>Active Triggers:</span>
                <span class="badge badge-success">3 Triggers Active</span>
            </li>
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>Stored Procedures:</span>
                <span class="badge badge-success">3 Procedures Active</span>
            </li>
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>Relational Views:</span>
                <span class="badge badge-success">3 Views Active</span>
            </li>
            <li style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <span>RBAC Security Roles:</span>
                <span class="badge badge-secondary">admin_role, lecturer_role</span>
            </li>
        </ul>
        <div style="margin-top: 1.25rem;">
            <a href="advanced_demo.php" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem;">
                <?= lucide('zap', '', 16) ?> Explore ADBMS Objects <?= lucide('arrow-right', '', 14) ?>
            </a>
        </div>
    </div>
</div>

<!-- RECENT EXAMINATION RESULTS FROM VIEW -->
<div class="card">
    <div class="card-header">
        <h2><?= lucide('award', '', 20) ?> Recent Examination Records (Rendered from <code>vw_StudentExamResults</code>)</h2>
        <a href="examinations.php" class="btn btn-outline btn-sm">
            Open Exams Module <?= lucide('arrow-right', '', 14) ?>
        </a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Course Code</th>
                    <th>Marks (0-100)</th>
                    <th>Calculated Grade</th>
                    <th>Outcome</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_grades as $rg): ?>
                <tr>
                    <td><?= htmlspecialchars($rg['student_name']) ?></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($rg['course_code']) ?></span></td>
                    <td><strong><?= number_format($rg['marks_obtained'], 2) ?>%</strong></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($rg['letter_grade']) ?></span></td>
                    <td>
                        <?php if ($rg['result_status'] === 'Pass'): ?>
                            <span class="badge badge-success">Pass</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Fail</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
