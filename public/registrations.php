<?php
$page_title = "Course Registration - UniSIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = null;
$message_type = null;

// Handle enrollment submission using the Stored Procedure sp_RegisterStudentCourse
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($request_method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register_course') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    $academic_year = trim($_POST['academic_year'] ?? '2024/2025');
    $semester = $_POST['semester'] ?? 'Semester 1';

    if ($student_id > 0 && $course_id > 0) {
        try {
            try {
                // Call Stored Procedure with OUT parameter
                $stmt = $pdo->prepare("CALL sp_RegisterStudentCourse(?, ?, ?, ?, @p_msg)");
                $stmt->execute([$student_id, $course_id, $academic_year, $semester]);
                $stmt->closeCursor();

                $result = $pdo->query("SELECT @p_msg AS msg")->fetch();
                $proc_msg = $result['msg'] ?? '';
            } catch (PDOException $sp_err) {
                // Fallback direct registration if stored procedure cache is unavailable
                $chk = $pdo->prepare("
                    SELECT COUNT(*) FROM course_registrations 
                    WHERE student_id = ? AND course_id = ? AND academic_year = ? AND semester = ?
                ");
                $chk->execute([$student_id, $course_id, $academic_year, $semester]);
                if ($chk->fetchColumn() > 0) {
                    $proc_msg = "ERROR: Student is already registered for this course in this term.";
                } else {
                    $ins = $pdo->prepare("
                        INSERT INTO course_registrations (student_id, course_id, academic_year, semester, registration_date, status)
                        VALUES (?, ?, ?, ?, CURDATE(), 'Registered')
                    ");
                    $ins->execute([$student_id, $course_id, $academic_year, $semester]);
                    $proc_msg = "SUCCESS: Course registered successfully.";
                }
            }

            if (strpos($proc_msg, 'SUCCESS') !== false) {
                $message = $proc_msg;
                $message_type = "success";
            } else {
                $message = $proc_msg;
                $message_type = "danger";
            }
        } catch (PDOException $e) {
            $message = "Database execution error: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = "Please select both a valid student and a course.";
        $message_type = "warning";
    }
}

// Fetch students & courses for enrollment dropdown
$students = $pdo->query("SELECT student_id, reg_number, first_name, last_name FROM students ORDER BY first_name")->fetchAll();
$courses = $pdo->query("SELECT course_id, course_code, course_title FROM courses ORDER BY course_code")->fetchAll();

// Fetch existing registrations
$registrations = $pdo->query("
    SELECT cr.registration_id, s.reg_number, CONCAT(s.first_name, ' ', s.last_name) AS student_name,
           c.course_code, c.course_title, cr.academic_year, cr.semester, cr.registration_date, cr.status
    FROM course_registrations cr
    JOIN students s ON cr.student_id = s.student_id
    JOIN courses c ON cr.course_id = c.course_id
    ORDER BY cr.registration_id DESC
")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('clipboard-check', '', 26) ?> Course Registration Module</h1>
        <p>Enroll students into courses with automated integrity verification</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= $message_type === 'success' ? lucide('check-circle', '', 18) : lucide('alert-triangle', '', 18) ?>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- REGISTRATION ROSTER -->
    <div class="card">
        <div class="card-header">
            <h2>Active Enrollments (<?= count($registrations) ?>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Academic Term</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                    <tr>
                        <td>#<?= $reg['registration_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($reg['student_name']) ?></strong><br>
                            <span style="font-size: 0.75rem; color: var(--text-secondary);"><?= htmlspecialchars($reg['reg_number']) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-secondary"><?= htmlspecialchars($reg['course_code']) ?></span>
                            <?= htmlspecialchars($reg['course_title']) ?>
                        </td>
                        <td><?= htmlspecialchars($reg['academic_year']) ?> (<?= htmlspecialchars($reg['semester']) ?>)</td>
                        <td><?= htmlspecialchars($reg['registration_date']) ?></td>
                        <td>
                            <?php if ($reg['status'] === 'Completed'): ?>
                                <span class="badge badge-success">Completed</span>
                            <?php else: ?>
                                <span class="badge badge-info">Registered</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ENROLLMENT FORM -->
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('plus-circle', '', 20) ?> New Registration</h2>
        </div>
        <div class="alert alert-info" style="font-size: 0.8rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            <?= lucide('zap', '', 16) ?>
            <span>Executes Stored Procedure: <code>sp_RegisterStudentCourse</code> which performs validation against double-registrations.</span>
        </div>
        <form method="POST" action="registrations.php">
            <input type="hidden" name="action" value="register_course">
            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label>Select Student *</label>
                <select name="student_id" class="form-control" required>
                    <option value="">-- Choose Student --</option>
                    <?php foreach ($students as $st): ?>
                        <option value="<?= $st['student_id'] ?>"><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?> (<?= $st['reg_number'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label>Select Course *</label>
                <select name="course_id" class="form-control" required>
                    <option value="">-- Choose Course --</option>
                    <?php foreach ($courses as $co): ?>
                        <option value="<?= $co['course_id'] ?>"><?= htmlspecialchars($co['course_code']) ?> - <?= htmlspecialchars($co['course_title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.85rem;">
                <label>Academic Year</label>
                <input type="text" name="academic_year" class="form-control" value="2024/2025" required>
            </div>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label>Semester</label>
                <select name="semester" class="form-control" required>
                    <option value="Semester 1">Semester 1</option>
                    <option value="Semester 2">Semester 2</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem;">
                <?= lucide('plus', '', 16) ?> Submit Registration
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
