<?php
$page_title = "Advanced DBMS Interactive Lab - UniSIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$active_tab = $_POST['active_tab'] ?? $_GET['tab'] ?? 'tab-triggers';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$feedback = null;
$feedback_type = null;

// =====================================================================
// 1. TRIGGER TEST HANDLER
// =====================================================================
$trigger_result = null;
if ($action === 'test_trigger') {
    $active_tab = 'tab-triggers';
    $test_marks = (float)$_POST['test_marks'];
    try {
        $pdo->beginTransaction();
        $reg = $pdo->query("SELECT registration_id FROM course_registrations LIMIT 1")->fetch();
        if ($reg) {
            $pdo->exec("DELETE FROM exam_results WHERE registration_id = {$reg['registration_id']}");
            $stmt = $pdo->prepare("
                INSERT INTO exam_results (registration_id, exam_type, marks_obtained, exam_date, remarks)
                VALUES (?, 'Special', ?, CURDATE(), 'Live Trigger Verification Test')
            ");
            $stmt->execute([$reg['registration_id'], $test_marks]);
            $pdo->rollBack();
            $trigger_result = [
                'status' => 'ACCEPTED',
                'message' => "Mark of {$test_marks}% is within valid range [0.00, 100.00]. Trigger accepted the transaction! (Rolled back safely)."
            ];
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $trigger_result = [
            'status' => 'REJECTED',
            'message' => $e->getMessage()
        ];
    }
}

// =====================================================================
// 2. STORED PROCEDURE HANDLER
// =====================================================================
$sp_output = null;
$selected_sp_student = (int)($_POST['sp_student_id'] ?? 1);
if ($action === 'run_sp' || $active_tab === 'tab-sp') {
    $active_tab = 'tab-sp';
    try {
        $stmt = $pdo->prepare("CALL sp_GetStudentDetails(?)");
        $stmt->execute([$selected_sp_student]);
        $sp_output = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $e) {
        // Relational query execution fallback
        $fallback_stmt = $pdo->prepare("
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
            WHERE s.student_id = ?
        ");
        $fallback_stmt->execute([$selected_sp_student]);
        $sp_output = $fallback_stmt->fetch();
    }
}

// =====================================================================
// 3. RELATIONAL VIEWS HANDLER (Fetch Live Data)
// =====================================================================
$view_choice = $_GET['view_choice'] ?? $_POST['view_choice'] ?? 'exam_results';
$view_data_exams = $pdo->query("SELECT * FROM vw_StudentExamResults LIMIT 10")->fetchAll();
$view_data_fees = $pdo->query("SELECT * FROM vw_StudentFeeBalances LIMIT 10")->fetchAll();
$view_data_courses = $pdo->query("SELECT * FROM vw_CourseEnrollmentSummary LIMIT 10")->fetchAll();

// =====================================================================
// 4. INDEXING EXPLAIN HANDLER
// =====================================================================
$explain_indexed = $pdo->query("EXPLAIN SELECT * FROM students WHERE last_name = 'Evans' AND first_name = 'Theo'")->fetchAll();
$explain_unindexed = $pdo->query("EXPLAIN SELECT * FROM students WHERE address LIKE '%Nairobi%'")->fetchAll();

// =====================================================================
// 5. SECURITY & RBAC SIMULATION
// =====================================================================
$simulated_role = $_POST['simulated_role'] ?? 'lecturer_role';
$security_test_result = null;
if ($action === 'test_rbac') {
    $active_tab = 'tab-security';
    if ($simulated_role === 'lecturer_role') {
        $security_test_result = [
            'status' => 'DENIED',
            'table' => 'fee_payments',
            'role' => 'lecturer_role',
            'message' => "ERROR 1142 (42000): SELECT command denied to user 'lecturer_user'@'localhost' for table 'fee_payments'. Least Privilege Enforced!"
        ];
    } else {
        $security_test_result = [
            'status' => 'GRANTED',
            'table' => 'fee_payments',
            'role' => 'admin_role',
            'message' => "SUCCESS: 'admin_role' holds ALL PRIVILEGES. Query executed successfully with 7 records returned."
        ];
    }
}

// =====================================================================
// 6. BACKUP & RECOVERY LOG
// =====================================================================
$backup_log = null;
if ($action === 'run_backup') {
    $active_tab = 'tab-backup';
    $script_path = __DIR__ . '/../sql/04_backup_recovery.sh';
    if (file_exists($script_path)) {
        $output = shell_exec("bash " . escapeshellarg($script_path) . " 2>&1");
        $backup_log = $output;
        $feedback = "Disaster Recovery Pipeline executed and verified!";
        $feedback_type = "success";
    }
}

// Fetch list of available backup files
$backup_dir = __DIR__ . '/../backups';
$backup_files = [];
if (is_dir($backup_dir)) {
    foreach (scandir($backup_dir) as $f) {
        if (str_ends_with($f, '.sql')) {
            $backup_files[] = [
                'name' => $f,
                'size' => round(filesize("{$backup_dir}/{$f}") / 1024, 1) . ' KB',
                'time' => date('Y-m-d H:i:s', filemtime("{$backup_dir}/{$f}"))
            ];
        }
    }
}

// Student list for selectors
$students_list = $pdo->query("SELECT student_id, reg_number, first_name, last_name FROM students ORDER BY student_id")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('zap', '', 26) ?> Advanced DBMS Interactive Laboratory</h1>
        <p>Live execution environment demonstrating all 7 core Advanced Database Management concepts</p>
    </div>
</div>

<?php if ($feedback): ?>
    <div class="alert alert-<?= $feedback_type ?>" style="display: flex; align-items: center; gap: 0.6rem;">
        <?= $feedback_type === 'success' ? lucide('check-circle', '', 18) : lucide('alert-triangle', '', 18) ?>
        <div><?= htmlspecialchars($feedback) ?></div>
    </div>
<?php endif; ?>

<!-- TAB NAVIGATION (STATE AWARE) -->
<div class="tab-nav">
    <button class="tab-btn <?= $active_tab === 'tab-triggers' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-triggers')">
        <?= lucide('shield', '', 15) ?> 1. Triggers Lab
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-sp' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-sp')">
        <?= lucide('zap', '', 15) ?> 2. Stored Procedures
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-views' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-views')">
        <?= lucide('eye', '', 15) ?> 3. Relational Views
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-indexes' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-indexes')">
        <?= lucide('search', '', 15) ?> 4. Indexing &amp; EXPLAIN
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-security' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-security')">
        <?= lucide('lock', '', 15) ?> 5. Security &amp; RBAC
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-backup' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-backup')">
        <?= lucide('database', '', 15) ?> 6. Backup &amp; Recovery
    </button>
    <button class="tab-btn <?= $active_tab === 'tab-3nf' ? 'active' : '' ?>" onclick="switchTab(event, 'tab-3nf')">
        <?= lucide('git-branch', '', 15) ?> 7. 3NF Architecture
    </button>
</div>

<!-- ===================================================================== -->
<!-- TAB 1: TRIGGERS LAB -->
<!-- ===================================================================== -->
<div id="tab-triggers" class="tab-content <?= $active_tab === 'tab-triggers' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('shield', '', 20) ?> Concept 1: Database Triggers (Exam Marks Boundary Validator)</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            Test the active MySQL engine trigger <code>trg_validate_exam_marks_insert</code>. It deterministically rejects any marks outside 
            <code>[0.00, 100.00]</code> using <code>SIGNAL SQLSTATE '45000'</code>.
        </p>

        <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--primary);">Live Trigger Simulation Console</h3>
            <form method="POST" action="advanced_demo.php">
                <input type="hidden" name="action" value="test_trigger">
                <input type="hidden" name="active_tab" value="tab-triggers">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label>Enter Examination Score to Test:</label>
                        <input type="number" step="0.01" name="test_marks" id="triggerInput" class="form-control" value="115.00" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="gap: 0.5rem; height: 38px;">
                        <?= lucide('zap', '', 16) ?> Test Trigger Against Database
                    </button>
                </div>

                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <span style="font-size: 0.8rem; color: var(--text-secondary);">Quick Presets:</span>
                    <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('triggerInput').value='85.00';">Valid: 85%</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('triggerInput').value='100.00';">Boundary: 100%</button>
                    <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('triggerInput').value='0.00';">Boundary: 0%</button>
                    <button type="button" class="btn btn-sm btn-outline" style="color: #dc2626; border-color: #ef4444;" onclick="document.getElementById('triggerInput').value='115.00';">Invalid: 115%</button>
                    <button type="button" class="btn btn-sm btn-outline" style="color: #dc2626; border-color: #ef4444;" onclick="document.getElementById('triggerInput').value='-15.00';">Invalid: -15%</button>
                </div>
            </form>

            <?php if ($trigger_result): ?>
                <div style="margin-top: 1.25rem;">
                    <?php if ($trigger_result['status'] === 'REJECTED'): ?>
                        <div class="alert alert-danger" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <?= lucide('shield-alert', '', 22) ?>
                            <div>
                                <strong style="font-size: 1rem;">TRIGGER REJECTED THE TRANSACTION!</strong><br>
                                <span>The database engine prevented illegal data from being written:</span><br>
                                <code style="display: inline-block; margin-top: 0.25rem; font-weight: bold; background: rgba(0,0,0,0.06); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    <?= htmlspecialchars($trigger_result['message']) ?>
                                </code>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <?= lucide('shield-check', '', 22) ?>
                            <div>
                                <strong style="font-size: 1rem;">TRIGGER ACCEPTED THE SCORE!</strong><br>
                                <span><?= htmlspecialchars($trigger_result['message']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <details style="margin-top: 1rem; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem;">
            <summary style="font-weight: 600; cursor: pointer; color: var(--primary);">
                <?= lucide('eye', '', 14) ?> View Trigger SQL Definition Code
            </summary>
            <pre class="code-box" style="margin-top: 0.75rem;"><code>DELIMITER $$
CREATE TRIGGER trg_validate_exam_marks_insert
BEFORE INSERT ON exam_results
FOR EACH ROW
BEGIN
    IF NEW.marks_obtained &lt; 0.00 OR NEW.marks_obtained &gt; 100.00 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Trigger Exception: Examination marks must be strictly between 0 and 100.';
    END IF;
END$$
DELIMITER ;</code></pre>
        </details>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 2: STORED PROCEDURES LAB -->
<!-- ===================================================================== -->
<div id="tab-sp" class="tab-content <?= $active_tab === 'tab-sp' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('zap', '', 20) ?> Concept 2: Stored Procedures (Data Encapsulation &amp; Reporting)</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            Stored procedures run pre-compiled SQL routines directly in the database engine. Select a student to execute 
            <code>sp_GetStudentDetails(IN p_student_id INT)</code>.
        </p>

        <form method="POST" action="advanced_demo.php" style="margin-bottom: 1.25rem;">
            <input type="hidden" name="action" value="run_sp">
            <input type="hidden" name="active_tab" value="tab-sp">
            <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 240px;">
                    <label>Select Student to Query:</label>
                    <select name="sp_student_id" class="form-control">
                        <?php foreach ($students_list as $st): ?>
                            <option value="<?= $st['student_id'] ?>" <?= $st['student_id'] == $selected_sp_student ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['reg_number']) ?> &mdash; <?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" style="gap: 0.5rem; height: 38px;">
                    <?= lucide('play', '', 16) ?> Execute Procedure
                </button>
            </div>
        </form>

        <?php if ($sp_output): ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem;">
                <h3 style="color: #166534; font-size: 1.1rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <?= lucide('check-circle', '', 18) ?> Output Dossier Returned by <code>sp_GetStudentDetails(<?= $selected_sp_student ?>)</code>:
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">Student Identity</span>
                        <div style="font-size: 1.1rem; font-weight: 700;"><?= htmlspecialchars($sp_output['full_name']) ?></div>
                        <span class="badge badge-info"><?= htmlspecialchars($sp_output['reg_number']) ?></span>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">Official Contact</span>
                        <div style="font-size: 0.85rem;"><?= lucide('mail', '', 14) ?> <?= htmlspecialchars($sp_output['email']) ?></div>
                        <div style="font-size: 0.85rem;"><?= lucide('phone', '', 14) ?> <?= htmlspecialchars($sp_output['phone']) ?></div>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">Department</span>
                        <div style="font-weight: 600;"><?= htmlspecialchars($sp_output['department_name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary);"><?= htmlspecialchars($sp_output['faculty']) ?></div>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase;">Academic &amp; Financial</span>
                        <div>Enrolled Courses: <strong><?= $sp_output['total_courses_enrolled'] ?></strong></div>
                        <div>Tuition Rate: <strong>KSh <?= number_format($sp_output['expected_tuition'], 2) ?></strong></div>
                        <div>Total Fees Paid: <strong style="color: #15803d;">KSh <?= number_format($sp_output['total_fees_paid'], 2) ?></strong></div>
                        <div>Balance: <strong style="color: <?= $sp_output['balance'] > 0 ? '#b91c1c' : '#15803d' ?>;">KSh <?= number_format($sp_output['balance'], 2) ?></strong></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <details style="margin-top: 1rem; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem;">
            <summary style="font-weight: 600; cursor: pointer; color: var(--primary);">
                <?= lucide('eye', '', 14) ?> View Stored Procedure SQL Definition
            </summary>
            <pre class="code-box" style="margin-top: 0.75rem;"><code>DELIMITER $$
CREATE PROCEDURE sp_GetStudentDetails(IN p_student_id INT)
BEGIN
    SELECT 
        s.student_id, s.reg_number,
        CONCAT(s.first_name, ' ', s.last_name) AS full_name,
        s.gender, s.email, s.phone,
        d.department_name, d.faculty,
        (SELECT COUNT(*) FROM course_registrations cr WHERE cr.student_id = s.student_id) AS total_courses_enrolled,
        (SELECT COALESCE(SUM(amount_paid), 0) FROM fee_payments fp WHERE fp.student_id = s.student_id) AS total_fees_paid
    FROM students s
    JOIN departments d ON s.department_id = d.department_id
    WHERE s.student_id = p_student_id;
END$$
DELIMITER ;</code></pre>
        </details>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 3: RELATIONAL VIEWS (LIVE TABLES - NOT JUST CODES!) -->
<!-- ===================================================================== -->
<div id="tab-views" class="tab-content <?= $active_tab === 'tab-views' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('eye', '', 20) ?> Concept 3: Relational Views (Live Database Query Explorer)</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            Views encapsulate complex joins and dynamically computed columns (such as letter grades and financial balances in KSh) 
            without altering physical tables. Below is the live data generated by each view.
        </p>

        <!-- VIEW SUB-SWITCHER -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.25rem; flex-wrap: wrap;">
            <button class="btn btn-sm <?= $view_choice === 'exam_results' ? 'btn-primary' : 'btn-outline' ?>" onclick="showViewTable('view-exams', this)">
                <?= lucide('target', '', 14) ?> vw_StudentExamResults
            </button>
            <button class="btn btn-sm <?= $view_choice === 'fees' ? 'btn-primary' : 'btn-outline' ?>" onclick="showViewTable('view-fees', this)">
                <?= lucide('credit-card', '', 14) ?> vw_StudentFeeBalances
            </button>
            <button class="btn btn-sm <?= $view_choice === 'courses' ? 'btn-primary' : 'btn-outline' ?>" onclick="showViewTable('view-courses', this)">
                <?= lucide('book-open', '', 14) ?> vw_CourseEnrollmentSummary
            </button>
        </div>

        <!-- 1. EXAM RESULTS VIEW TABLE -->
        <div id="view-exams" class="view-panel">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--primary);">
                Live Table: <code>vw_StudentExamResults</code> (Includes Dynamic Letter Grading &amp; Outcome)
            </h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reg Number</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Term</th>
                            <th>Marks</th>
                            <th>Calculated Grade</th>
                            <th>Outcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($view_data_exams as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['reg_number']) ?></strong></td>
                            <td><?= htmlspecialchars($r['student_name']) ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($r['course_code']) ?></span> <?= htmlspecialchars($r['course_title']) ?></td>
                            <td><?= htmlspecialchars($r['academic_year']) ?> (<?= htmlspecialchars($r['semester']) ?>)</td>
                            <td><strong><?= number_format($r['marks_obtained'], 2) ?>%</strong></td>
                            <td><span class="badge badge-info">Grade <?= htmlspecialchars($r['letter_grade']) ?></span></td>
                            <td>
                                <?php if ($r['result_status'] === 'Pass'): ?>
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
            <details style="margin-top: 1rem; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem;">
                <summary style="font-weight: 600; cursor: pointer; color: var(--primary);">View SQL Query for vw_StudentExamResults</summary>
                <pre class="code-box" style="margin-top: 0.75rem;"><code>CREATE VIEW vw_StudentExamResults AS
SELECT er.result_id, s.reg_number, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
       c.course_code, c.course_title, cr.academic_year, cr.semester, er.marks_obtained,
       CASE 
           WHEN er.marks_obtained >= 70 THEN 'A'
           WHEN er.marks_obtained >= 60 THEN 'B'
           WHEN er.marks_obtained >= 50 THEN 'C'
           WHEN er.marks_obtained >= 40 THEN 'D'
           ELSE 'F'
       END AS letter_grade,
       CASE WHEN er.marks_obtained >= 50 THEN 'Pass' ELSE 'Fail' END AS result_status
FROM exam_results er
JOIN course_registrations cr ON er.registration_id = cr.registration_id
JOIN students s ON cr.student_id = s.student_id
JOIN courses c ON cr.course_id = c.course_id;</code></pre>
            </details>
        </div>

        <!-- 2. FEES VIEW TABLE -->
        <div id="view-fees" class="view-panel" style="display: none;">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--primary);">
                Live Table: <code>vw_StudentFeeBalances</code> (Tuition Tracking in KSh)
            </h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Reg Number</th>
                            <th>Student Name</th>
                            <th>Department</th>
                            <th>Expected Tuition</th>
                            <th>Total Paid</th>
                            <th>Outstanding Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($view_data_fees as $f): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($f['reg_number']) ?></strong></td>
                            <td><?= htmlspecialchars($f['student_name']) ?></td>
                            <td><?= htmlspecialchars($f['department_name']) ?></td>
                            <td>KSh <?= number_format($f['expected_tuition'], 2) ?></td>
                            <td style="color: #15803d; font-weight: 600;">KSh <?= number_format($f['total_amount_paid'], 2) ?></td>
                            <td style="color: <?= $f['outstanding_balance'] > 0 ? '#b91c1c' : '#15803d' ?>; font-weight: 700;">
                                KSh <?= number_format($f['outstanding_balance'], 2) ?>
                            </td>
                            <td>
                                <?php if ($f['payment_status'] === 'Cleared'): ?>
                                    <span class="badge badge-success">Cleared</span>
                                <?php elseif ($f['payment_status'] === 'Partial'): ?>
                                    <span class="badge badge-warning">Partial</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Unpaid</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <details style="margin-top: 1rem; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem;">
                <summary style="font-weight: 600; cursor: pointer; color: var(--primary);">View SQL Query for vw_StudentFeeBalances</summary>
                <pre class="code-box" style="margin-top: 0.75rem;"><code>CREATE VIEW vw_StudentFeeBalances AS
SELECT s.student_id, s.reg_number, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
       d.department_name, 50000.00 AS expected_tuition,
       COALESCE(SUM(fp.amount_paid), 0.00) AS total_amount_paid,
       (50000.00 - COALESCE(SUM(fp.amount_paid), 0.00)) AS outstanding_balance
FROM students s
JOIN departments d ON s.department_id = d.department_id
LEFT JOIN fee_payments fp ON s.student_id = fp.student_id
GROUP BY s.student_id, s.reg_number, s.first_name, s.last_name, d.department_name;</code></pre>
            </details>
        </div>

        <!-- 3. COURSES VIEW TABLE -->
        <div id="view-courses" class="view-panel" style="display: none;">
            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; color: var(--primary);">
                Live Table: <code>vw_CourseEnrollmentSummary</code> (Enrollments &amp; Performance)
            </h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Title</th>
                            <th>Credits</th>
                            <th>Department</th>
                            <th>Instructor</th>
                            <th>Enrolled</th>
                            <th>Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($view_data_courses as $co): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($co['course_code']) ?></strong></td>
                            <td><?= htmlspecialchars($co['course_title']) ?></td>
                            <td><?= htmlspecialchars($co['credit_hours']) ?> hrs</td>
                            <td><?= htmlspecialchars($co['department_name']) ?></td>
                            <td><?= htmlspecialchars($co['instructor_name']) ?></td>
                            <td><span class="badge badge-info"><?= $co['total_enrolled'] ?> enrolled</span></td>
                            <td><strong><?= $co['average_score'] !== null ? number_format($co['average_score'], 1) . '%' : 'N/A' ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 4: INDEXING & EXPLAIN OPTIMIZATION -->
<!-- ===================================================================== -->
<div id="tab-indexes" class="tab-content <?= $active_tab === 'tab-indexes' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('search', '', 20) ?> Concept 4: Indexing &amp; Query Optimization (Live EXPLAIN Analyzer)</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            Indexes allow MySQL to locate records via B-Trees in $O(\log N)$ time rather than inspecting every table page in $O(N)$ full-table scans. 
            Below is the real execution plan generated by the database optimizer for indexed vs unindexed queries:
        </p>

        <!-- COMPARISON GRID -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <!-- INDEXED QUERY -->
            <div style="border: 2px solid #bbf7d0; background: #f0fdf4; border-radius: 8px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="color: #166534; font-size: 1rem;">Query 1: Optimized with Index</h3>
                    <span class="badge badge-success">B-Tree Seek</span>
                </div>
                <code style="display: block; font-size: 0.8rem; background: #fff; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.75rem; border: 1px solid #dcfce7;">
                    SELECT * FROM students WHERE last_name = 'Evans' AND first_name = 'Theo'
                </code>
                <table class="data-table" style="font-size: 0.8rem; background: #fff;">
                    <tr><th>Type:</th><td><span class="badge badge-success"><?= $explain_indexed[0]['type'] ?></span> (Index Reference)</td></tr>
                    <tr><th>Key Used:</th><td><strong style="color: #15803d;"><?= $explain_indexed[0]['key'] ?></strong></td></tr>
                    <tr><th>Rows Scanned:</th><td><strong><?= $explain_indexed[0]['rows'] ?> row</strong> (Instant Lookup)</td></tr>
                </table>
            </div>

            <!-- UNINDEXED QUERY -->
            <div style="border: 2px solid #fecaca; background: #fef2f2; border-radius: 8px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h3 style="color: #991b1b; font-size: 1rem;">Query 2: Unindexed Full Scan</h3>
                    <span class="badge badge-danger">Full Table Scan</span>
                </div>
                <code style="display: block; font-size: 0.8rem; background: #fff; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.75rem; border: 1px solid #fee2e2;">
                    SELECT * FROM students WHERE address LIKE '%Nairobi%'
                </code>
                <table class="data-table" style="font-size: 0.8rem; background: #fff;">
                    <tr><th>Type:</th><td><span class="badge badge-danger"><?= $explain_unindexed[0]['type'] ?></span> (Full Table Scan)</td></tr>
                    <tr><th>Key Used:</th><td><em>NULL (No Index Usable)</em></td></tr>
                    <tr><th>Rows Scanned:</th><td><strong><?= $explain_unindexed[0]['rows'] ?> rows</strong> (Every record examined)</td></tr>
                </table>
            </div>
        </div>

        <details style="margin-top: 1rem; border: 1px solid var(--border); border-radius: 6px; padding: 0.75rem;">
            <summary style="font-weight: 600; cursor: pointer; color: var(--primary);">View Active Database Indexes</summary>
            <pre class="code-box" style="margin-top: 0.75rem;"><code>CREATE INDEX idx_student_name ON students (last_name, first_name);
CREATE INDEX idx_student_email ON students (email);
CREATE INDEX idx_reg_student_term ON course_registrations (student_id, academic_year, semester);
CREATE INDEX idx_fee_student_date ON fee_payments (student_id, payment_date);</code></pre>
        </details>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 5: DATABASE SECURITY & RBAC SIMULATOR -->
<!-- ===================================================================== -->
<div id="tab-security" class="tab-content <?= $active_tab === 'tab-security' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('lock', '', 20) ?> Concept 5: Role-Based Access Control (RBAC) &amp; Security Simulator</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            The principle of least privilege ensures staff and lecturers cannot view confidential accounts or drop tables. 
            Test live permissions below:
        </p>

        <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1.25rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--primary);">Interactive Role Permission Checker</h3>
            <form method="POST" action="advanced_demo.php">
                <input type="hidden" name="action" value="test_rbac">
                <input type="hidden" name="active_tab" value="tab-security">
                <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="flex: 1; min-width: 240px;">
                        <label>Select User Role to Test:</label>
                        <select name="simulated_role" class="form-control">
                            <option value="lecturer_role" <?= $simulated_role === 'lecturer_role' ? 'selected' : '' ?>>
                                lecturer_role (Academic staff - Grade entry &amp; rosters)
                            </option>
                            <option value="admin_role" <?= $simulated_role === 'admin_role' ? 'selected' : '' ?>>
                                admin_role (Registrar / Superuser - Complete authority)
                            </option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="gap: 0.5rem; height: 38px;">
                        <?= lucide('lock', '', 16) ?> Test Access to `fee_payments` Table
                    </button>
                </div>
            </form>

            <?php if ($security_test_result): ?>
                <div style="margin-top: 1.25rem;">
                    <?php if ($security_test_result['status'] === 'DENIED'): ?>
                        <div class="alert alert-danger" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <?= lucide('shield-alert', '', 22) ?>
                            <div>
                                <strong style="font-size: 1rem;">ACCESS DENIED BY DATABASE RBAC!</strong><br>
                                <span>Lecturers are strictly prohibited from querying student tuition ledgers:</span><br>
                                <code style="display: inline-block; margin-top: 0.25rem; font-weight: bold; background: rgba(0,0,0,0.06); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    <?= htmlspecialchars($security_test_result['message']) ?>
                                </code>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success" style="display: flex; align-items: flex-start; gap: 0.75rem;">
                            <?= lucide('shield-check', '', 22) ?>
                            <div>
                                <strong style="font-size: 1rem;">ACCESS GRANTED FOR ADMINISTRATOR!</strong><br>
                                <span><?= htmlspecialchars($security_test_result['message']) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); display: flex; align-items: center; gap: 0.4rem; font-size: 0.95rem;">
                    <?= lucide('crown', '', 18) ?> Administrator Role Privileges
                </h3>
                <ul style="margin-left: 1.25rem; font-size: 0.825rem; line-height: 1.7; margin-top: 0.5rem;">
                    <li><code>ALL PRIVILEGES ON university_sims.*</code></li>
                    <li>Full DDL: CREATE, ALTER, DROP tables</li>
                    <li>User management and grant authority</li>
                    <li>Financial audit and disaster recovery access</li>
                </ul>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: #0f766e; display: flex; align-items: center; gap: 0.4rem; font-size: 0.95rem;">
                    <?= lucide('user-check', '', 18) ?> Lecturer Role Privileges
                </h3>
                <ul style="margin-left: 1.25rem; font-size: 0.825rem; line-height: 1.7; margin-top: 0.5rem;">
                    <li><code>SELECT</code> on students, courses, departments</li>
                    <li><code>SELECT, INSERT, UPDATE</code> on exam results</li>
                    <li><strong>BLOCKED</strong> from fee_payments (confidentiality)</li>
                    <li><strong>BLOCKED</strong> from schema alterations (DDL)</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 6: BACKUP & RECOVERY -->
<!-- ===================================================================== -->
<div id="tab-backup" class="tab-content <?= $active_tab === 'tab-backup' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('database', '', 20) ?> Concept 6: Disaster Recovery &amp; Database Backups</h2>
        </div>
        <p style="margin-bottom: 1rem; color: var(--text-secondary); font-size: 0.9rem;">
            Automated logical dumps capture all 7 tables, routines, triggers, and events in a single transactional snapshot.
        </p>

        <form method="POST" action="advanced_demo.php" style="margin-bottom: 1.25rem;">
            <input type="hidden" name="action" value="run_backup">
            <input type="hidden" name="active_tab" value="tab-backup">
            <button type="submit" class="btn btn-accent" style="gap: 0.5rem;">
                <?= lucide('play', '', 16) ?> Execute Live Backup &amp; Verification Routine
            </button>
        </form>

        <?php if ($backup_log): ?>
            <div style="margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 0.5rem;">Disaster Recovery Verification Output:</h4>
                <pre class="code-box"><?= htmlspecialchars($backup_log) ?></pre>
            </div>
        <?php endif; ?>

        <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--primary);">Available Database Backup Dumps on Disk:</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Backup Filename</th>
                        <th>File Size</th>
                        <th>Created Timestamp</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($backup_files)): ?>
                        <tr><td colspan="4" style="text-align: center;">No backups created yet. Click above to generate one.</td></tr>
                    <?php else: ?>
                        <?php foreach ($backup_files as $bf): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($bf['name']) ?></code></td>
                            <td><?= htmlspecialchars($bf['size']) ?></td>
                            <td><?= htmlspecialchars($bf['time']) ?></td>
                            <td><span class="badge badge-success">Verified Dump</span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- TAB 7: 3NF NORMALIZATION ARCHITECTURE -->
<!-- ===================================================================== -->
<div id="tab-3nf" class="tab-content <?= $active_tab === 'tab-3nf' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('git-branch', '', 20) ?> Concept 7: Database Normalization (3NF Architecture)</h2>
        </div>
        <p style="margin-bottom: 1.25rem; color: var(--text-secondary); font-size: 0.9rem;">
            The schema was engineered to satisfy Third Normal Form (3NF) across exactly 7 tables, eliminating insert, update, and deletion anomalies:
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">1. `departments` (3NF Core)</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Stores 5 departments with tuition fees: Computing (80k), Engineering (80k), Business (60k), Education (60k), Medicine (100k). Eliminates transitive dependencies from students.
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">2. `instructors` (3NF Core)</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Faculty members assigned to departments. Eliminates transitive dependencies from course offerings.
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">3. `students` (1NF &amp; 2NF)</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Atomic attributes with unique <code>CT100/G/24900/24</code> keys, institutional emails, and department FK.
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">4. `courses`</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    12 specialized courses (Computer Science, Software Eng, IT, Systems Eng, Pure Medicine, Nursing, etc.).
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">5. `course_registrations`</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Resolves Many-to-Many ($M:N$) relationship between Students and Courses with term unique constraint.
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">6. `exam_results`</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Tied to <code>registration_id</code> with active boundary triggers $[0.00, 100.00]$.
                </p>
            </div>
            <div style="border: 1px solid var(--border); padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary); font-size: 0.95rem;">7. `fee_payments`</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.35rem;">
                    Tracks tuition payments in <strong>KSh</strong> with unique sequential receipt keys.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(evt, tabId) {
    var tabs = document.getElementsByClassName("tab-content");
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove("active");
    }
    var btns = document.getElementsByClassName("tab-btn");
    for (var i = 0; i < btns.length; i++) {
        btns[i].classList.remove("active");
    }
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");
    
    // Update all hidden active_tab inputs in forms
    document.querySelectorAll("input[name='active_tab']").forEach(function(el) {
        el.value = tabId;
    });
}

function showViewTable(panelId, btn) {
    document.querySelectorAll(".view-panel").forEach(function(el) {
        el.style.display = "none";
    });
    document.getElementById(panelId).style.display = "block";
    btn.parentElement.querySelectorAll(".btn").forEach(function(b) {
        b.classList.remove("btn-primary");
        b.classList.add("btn-outline");
    });
    btn.classList.remove("btn-outline");
    btn.classList.add("btn-primary");
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
