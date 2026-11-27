<?php
$page_title = "Examination Management - UniSIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = null;
$message_type = null;

// Handle exam result entry
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($request_method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'record_marks') {
    $registration_id = (int)($_POST['registration_id'] ?? 0);
    $exam_type = $_POST['exam_type'] ?? 'Main';
    $marks_obtained = (float)($_POST['marks_obtained'] ?? 0.0);
    $exam_date = $_POST['exam_date'] ?? date('Y-m-d');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($registration_id > 0) {
        try {
            // Attempt to insert marks (Triggers trg_validate_exam_marks_insert)
            $stmt = $pdo->prepare("
                INSERT INTO exam_results (registration_id, exam_type, marks_obtained, exam_date, remarks)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$registration_id, $exam_type, $marks_obtained, $exam_date, $remarks]);
            $message = "SUCCESS: Exam marks ({$marks_obtained}%) recorded successfully! Trigger 'trg_update_registration_status' updated course registration to Completed.";
            $message_type = "success";
        } catch (PDOException $e) {
            // Catch the trigger SIGNAL error (SQLSTATE 45000)
            $message = "DATABASE TRIGGER ENFORCEMENT: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = "Please select a valid course registration.";
        $message_type = "warning";
    }
}

// Fetch registrations that do NOT have marks recorded yet
$pending_registrations = $pdo->query("
    SELECT cr.registration_id, s.reg_number, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
           c.course_code, c.course_title
    FROM course_registrations cr
    JOIN students s ON cr.student_id = s.student_id
    JOIN courses c ON cr.course_id = c.course_id
    LEFT JOIN exam_results er ON cr.registration_id = er.registration_id
    WHERE er.result_id IS NULL
    ORDER BY cr.registration_id DESC
")->fetchAll();

// Fetch results directly from the VIEW
$exam_results = $pdo->query("SELECT * FROM vw_StudentExamResults ORDER BY result_id DESC")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('target', '', 26) ?> Examination Management Module</h1>
        <p>Record exam evaluations, enforce boundary triggers (0–100), and inspect graded results</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>" style="display: flex; align-items: center; gap: 0.6rem;">
        <?= $message_type === 'danger' ? lucide('shield-alert', '', 20) : lucide('check-circle', '', 20) ?>
        <div>
            <strong><?= $message_type === 'danger' ? 'EXCEPTION' : 'NOTIFICATION' ?>:</strong> <?= htmlspecialchars($message) ?>
        </div>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- EXAM RESULTS LEDGER (FROM VIEW) -->
    <div class="card">
        <div class="card-header">
            <h2>Official Grade Sheet (Queried from <code>vw_StudentExamResults</code>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Term</th>
                        <th>Marks</th>
                        <th>Grade</th>
                        <th>Status</th>
                        <th>Exam Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($exam_results)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-secondary);">No examination records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($exam_results as $res): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($res['student_name']) ?></strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-secondary);"><?= htmlspecialchars($res['reg_number']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= htmlspecialchars($res['course_code']) ?></span><br>
                                <span style="font-size: 0.8rem;"><?= htmlspecialchars($res['course_title']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($res['academic_year']) ?><br><small><?= htmlspecialchars($res['semester']) ?></small></td>
                            <td><strong><?= number_format($res['marks_obtained'], 2) ?>%</strong></td>
                            <td>
                                <span class="badge <?= $res['letter_grade'] === 'F' ? 'badge-danger' : 'badge-info' ?>">
                                    Grade <?= htmlspecialchars($res['letter_grade']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($res['result_status'] === 'Pass'): ?>
                                    <span class="badge badge-success">Pass</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Fail</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?= htmlspecialchars($res['exam_date']) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- RECORD MARKS FORM WITH TRIGGER VERIFICATION -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2><?= lucide('plus-circle', '', 20) ?> Record Examination Marks</h2>
            </div>
            <div class="alert alert-info" style="font-size: 0.8rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <?= lucide('shield', '', 18) ?>
                <span><strong>Active DB Trigger:</strong> <code>trg_validate_exam_marks_insert</code> enforces that marks must be within <code>[0.00, 100.00]</code>.</span>
            </div>

            <form method="POST" action="examinations.php">
                <input type="hidden" name="action" value="record_marks">
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label>Select Enrolled Registration *</label>
                    <select name="registration_id" class="form-control" required>
                        <?php if (empty($pending_registrations)): ?>
                            <option value="">-- All existing enrollments already have grades --</option>
                        <?php else: ?>
                            <option value="">-- Choose Registration --</option>
                            <?php foreach ($pending_registrations as $pr): ?>
                                <option value="<?= $pr['registration_id'] ?>">
                                    #<?= $pr['registration_id'] ?>: <?= htmlspecialchars($pr['student_name']) ?> (<?= $pr['course_code'] ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label>Exam Assessment Type</label>
                    <select name="exam_type" class="form-control">
                        <option value="Main">Main Examination</option>
                        <option value="Supplementary">Supplementary Exam</option>
                        <option value="Special">Special Assessment</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label>Marks Obtained (0.00 to 100.00) *</label>
                    <input type="number" step="0.01" name="marks_obtained" id="marksInput" class="form-control" placeholder="e.g. 78.50" required>
                    <small style="color: var(--text-secondary);">Try entering <code>115</code> or <code>-10</code> to test trigger rejection!</small>
                </div>
                <div class="form-group" style="margin-bottom: 0.85rem;">
                    <label>Exam Date</label>
                    <input type="date" name="exam_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label>Evaluator Remarks</label>
                    <input type="text" name="remarks" class="form-control" placeholder="Optional comments">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem;">
                    <?= lucide('plus', '', 16) ?> Commit Exam Record
                </button>
            </form>
        </div>

        <!-- QUICK TEST HELPER CARD -->
        <div class="card" style="background: #fffbeb; border: 1px solid #fef3c7;">
            <div class="card-header" style="border-color: #fde68a;">
                <h3 style="font-size: 1rem; color: #92400e; display: flex; align-items: center; gap: 0.4rem;">
                    <?= lucide('zap', '', 16) ?> Quick Trigger Test Values
                </h3>
            </div>
            <p style="font-size: 0.8rem; color: #78350f; margin-bottom: 0.75rem;">
                Click any preset button below to populate the marks field and test database trigger behavior:
            </p>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-sm btn-outline" onclick="document.getElementById('marksInput').value = '82.50';">
                    Valid: 82.5%
                </button>
                <button type="button" class="btn btn-sm btn-outline" style="border-color: #ef4444; color: #dc2626;" onclick="document.getElementById('marksInput').value = '115.00';">
                    Invalid: 115% (Triggers Error)
                </button>
                <button type="button" class="btn btn-sm btn-outline" style="border-color: #ef4444; color: #dc2626;" onclick="document.getElementById('marksInput').value = '-12.00';">
                    Invalid: -12% (Triggers Error)
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
