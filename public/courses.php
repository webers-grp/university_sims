<?php
$page_title = "Course Management - UniSIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = null;
$message_type = null;

// Handle Add Course
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($request_method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_course') {
    $course_code = strtoupper(trim($_POST['course_code'] ?? ''));
    $course_title = trim($_POST['course_title'] ?? '');
    $credit_hours = (int)($_POST['credit_hours'] ?? 3);
    $department_id = (int)($_POST['department_id'] ?? 0);
    $instructor_id = !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null;
    $academic_level = $_POST['academic_level'] ?? '100';

    if ($course_code && $course_title && $credit_hours >= 1 && $credit_hours <= 6 && $department_id > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO courses (course_code, course_title, credit_hours, department_id, instructor_id, academic_level)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$course_code, $course_title, $credit_hours, $department_id, $instructor_id, $academic_level]);
            $message = "Course {$course_code}: {$course_title} created successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error creating course: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = "Please verify all fields. Credit hours must be between 1 and 6.";
        $message_type = "warning";
    }
}

// Fetch departments & instructors for dropdowns
$departments = $pdo->query("SELECT department_id, department_code, department_name FROM departments ORDER BY department_name")->fetchAll();
$instructors = $pdo->query("SELECT instructor_id, instructor_code, first_name, last_name FROM instructors ORDER BY first_name")->fetchAll();

// Fetch summary from VIEW
$course_summary = $pdo->query("SELECT * FROM vw_CourseEnrollmentSummary ORDER BY course_code")->fetchAll();
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('book-open', '', 26) ?> Course Management Module</h1>
        <p>Curriculum structure, course allocations, and enrollment analytics</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= $message_type === 'success' ? lucide('check-circle', '', 18) : lucide('alert-triangle', '', 18) ?>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- COURSES OVERVIEW FROM VIEW -->
    <div class="card">
        <div class="card-header">
            <h2>Course Catalog &amp; Analytics (From <code>vw_CourseEnrollmentSummary</code>)</h2>
        </div>
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
                        <th>Avg Mark</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($course_summary as $c): ?>
                    <tr>
                        <td><strong><span class="badge badge-secondary"><?= htmlspecialchars($c['course_code']) ?></span></strong></td>
                        <td><?= htmlspecialchars($c['course_title']) ?></td>
                        <td><?= htmlspecialchars($c['credit_hours']) ?> hrs</td>
                        <td><?= htmlspecialchars($c['department_name']) ?></td>
                        <td><?= htmlspecialchars($c['instructor_name']) ?></td>
                        <td><span class="badge badge-info"><?= $c['total_enrolled'] ?> students</span></td>
                        <td>
                            <?php if ($c['average_score'] !== null): ?>
                                <strong style="color: #0f766e;"><?= number_format($c['average_score'], 1) ?>%</strong>
                            <?php else: ?>
                                <span style="color: var(--text-secondary); font-style: italic;">No exams yet</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD NEW COURSE FORM -->
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('plus-circle', '', 20) ?> Add New Course</h2>
        </div>
        <form method="POST" action="courses.php">
            <input type="hidden" name="action" value="add_course">
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Course Code *</label>
                <input type="text" name="course_code" class="form-control" placeholder="e.g. CS401" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Course Title *</label>
                <input type="text" name="course_title" class="form-control" placeholder="e.g. Distributed Database Systems" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Credit Hours (1 - 6) *</label>
                <input type="number" name="credit_hours" class="form-control" min="1" max="6" value="3" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Academic Level</label>
                <select name="academic_level" class="form-control">
                    <option value="100">100 Level (1st Year)</option>
                    <option value="200">200 Level (2nd Year)</option>
                    <option value="300">300 Level (3rd Year)</option>
                    <option value="400">400 Level (4th Year)</option>
                    <option value="Postgraduate">Postgraduate</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Department *</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['department_id'] ?>"><?= htmlspecialchars($d['department_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label>Assigned Instructor</label>
                <select name="instructor_id" class="form-control">
                    <option value="">-- None / TBA --</option>
                    <?php foreach ($instructors as $ins): ?>
                        <option value="<?= $ins['instructor_id'] ?>"><?= htmlspecialchars($ins['first_name'] . ' ' . $ins['last_name']) ?> (<?= $ins['instructor_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem;">
                <?= lucide('plus', '', 16) ?> Create Course
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
