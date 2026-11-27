<?php
$page_title = "Student Management - UniSIMS";
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = null;
$message_type = null;

// Handle student registration form submission
$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($request_method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_student') {
    $reg_number = trim($_POST['reg_number'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? 'Other';
    $dob = $_POST['dob'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $department_id = (int)($_POST['department_id'] ?? 0);
    $enrollment_date = $_POST['enrollment_date'] ?? date('Y-m-d');

    if ($reg_number && $first_name && $last_name && $email && $department_id > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO students (reg_number, first_name, last_name, gender, date_of_birth, email, phone, address, department_id, enrollment_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$reg_number, $first_name, $last_name, $gender, $dob, $email, $phone, $address, $department_id, $enrollment_date]);
            $message = "Student {$first_name} {$last_name} ({$reg_number}) registered successfully!";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Error registering student: " . $e->getMessage();
            $message_type = "danger";
        }
    } else {
        $message = "Please fill in all required fields.";
        $message_type = "warning";
    }
}

// Check if viewing profile via Stored Procedure
$student_profile = null;
$profile_id = isset($_GET['view_id']) ? (int)$_GET['view_id'] : 0;
if ($profile_id > 0) {
    try {
        $stmt_sp = $pdo->prepare("CALL sp_GetStudentDetails(?)");
        $stmt_sp->execute([$profile_id]);
        $student_profile = $stmt_sp->fetch();
        $stmt_sp->closeCursor();
    } catch (PDOException $e) {
        // Direct query execution fallback if procedure cache is unavailable
        $stmt_fb = $pdo->prepare("
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
        $stmt_fb->execute([$profile_id]);
        $student_profile = $stmt_fb->fetch();
    }
}

// Fetch all departments for dropdown with tuition fee
$departments = $pdo->query("SELECT department_id, department_code, department_name, tuition_fee FROM departments ORDER BY department_name")->fetchAll();

// Fetch all students
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt_st = $pdo->prepare("
        SELECT s.*, d.department_name, d.department_code 
        FROM students s
        JOIN departments d ON s.department_id = d.department_id
        WHERE s.first_name LIKE ? OR s.last_name LIKE ? OR s.reg_number LIKE ? OR s.email LIKE ?
        ORDER BY s.student_id DESC
    ");
    $term = "%{$search}%";
    $stmt_st->execute([$term, $term, $term, $term]);
    $students = $stmt_st->fetchAll();
} else {
    $students = $pdo->query("
        SELECT s.*, d.department_name, d.department_code 
        FROM students s
        JOIN departments d ON s.department_id = d.department_id
        ORDER BY s.student_id DESC
    ")->fetchAll();
}
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('users', '', 26) ?> Student Management Module</h1>
        <p>Register, query, and manage university student profiles and records</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $message_type ?>">
        <?= $message_type === 'success' ? lucide('check-circle', '', 18) : lucide('alert-triangle', '', 18) ?>
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- DISPLAY PROFILE FROM STORED PROCEDURE IF SELECTED -->
<?php if ($student_profile): ?>
<div class="card" style="border: 2px solid var(--accent); background: #f0fdfa;">
    <div class="card-header" style="border-color: #99f6e4;">
        <h2 style="color: #0f766e; display: flex; align-items: center; gap: 0.5rem;">
            <?= lucide('zap', '', 20) ?> Student Profile Details (Executed via Stored Procedure: <code>sp_GetStudentDetails</code>)
        </h2>
        <a href="students.php" class="btn btn-outline btn-sm">Close Profile &times;</a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
        <div>
            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Full Name</span>
            <div style="font-size: 1.15rem; font-weight: 700;"><?= htmlspecialchars($student_profile['full_name']) ?></div>
            <span class="badge badge-info"><?= htmlspecialchars($student_profile['reg_number']) ?></span>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Department & Faculty</span>
            <div style="font-weight: 600;"><?= htmlspecialchars($student_profile['department_name']) ?></div>
            <div style="font-size: 0.825rem; color: var(--text-secondary);"><?= htmlspecialchars($student_profile['faculty']) ?></div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Contact & Address</span>
            <div><?= lucide('mail', '', 14) ?> <?= htmlspecialchars($student_profile['email']) ?></div>
            <div><?= lucide('phone', '', 14) ?> <?= htmlspecialchars($student_profile['phone']) ?></div>
            <div style="font-size: 0.825rem; color: var(--text-secondary); margin-top: 0.2rem;"><?= lucide('map-pin', '', 14) ?> <?= htmlspecialchars($student_profile['address']) ?></div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Academic Status</span>
            <div><span class="badge badge-success"><?= htmlspecialchars($student_profile['status']) ?></span></div>
            <div style="font-size: 0.825rem; margin-top: 0.25rem;">Enrolled: <?= htmlspecialchars($student_profile['enrollment_date']) ?></div>
        </div>
        <div>
            <span style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Aggregate Stats</span>
            <div>Courses Enrolled: <strong><?= $student_profile['total_courses_enrolled'] ?></strong></div>
            <div>Fees Paid: <strong style="color: #15803d;">KSh <?= number_format($student_profile['total_fees_paid'], 2) ?></strong></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- STUDENT ROSTER -->
    <div class="card">
        <div class="card-header">
            <h2>Student Directory (<?= count($students) ?>)</h2>
            <form method="GET" action="students.php" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" class="form-control" placeholder="Search name, reg, email..." value="<?= htmlspecialchars($search) ?>" style="width: 200px;">
                <button type="submit" class="btn btn-outline btn-sm"><?= lucide('search', '', 14) ?> Filter</button>
                <?php if ($search): ?>
                    <a href="students.php" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--text-secondary);">No student records found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($students as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['reg_number']) ?></strong></td>
                            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($row['department_code']) ?></span></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($row['status']) ?></span></td>
                            <td>
                                <a href="students.php?view_id=<?= $row['student_id'] ?>" class="btn btn-outline btn-sm" title="Invoke sp_GetStudentDetails">
                                    <?= lucide('zap', '', 14) ?> View SP
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- STUDENT REGISTRATION FORM -->
    <div class="card">
        <div class="card-header">
            <h2><?= lucide('plus-circle', '', 20) ?> Register New Student</h2>
        </div>
        <form method="POST" action="students.php">
            <input type="hidden" name="action" value="add_student">
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Registration Number *</label>
                <input type="text" name="reg_number" class="form-control" placeholder="e.g. CT100/G/24900/24" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>First Name *</label>
                <input type="text" name="first_name" class="form-control" placeholder="e.g. Theo" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Last Name *</label>
                <input type="text" name="last_name" class="form-control" placeholder="e.g. Evans" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Gender *</label>
                <select name="gender" class="form-control" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Date of Birth *</label>
                <input type="date" name="dob" class="form-control" value="2003-05-14" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. theoe.24900@students.kyu.ac.ke" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Phone Number *</label>
                <input type="text" name="phone" class="form-control" placeholder="+254-722-000000" required>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Department (3NF Foreign Key) *</label>
                <select name="department_id" class="form-control" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $d): ?>
                        <option value="<?= $d['department_id'] ?>">
                            <?= htmlspecialchars($d['department_name']) ?> (KSh <?= number_format($d['tuition_fee'], 0) ?>/sem)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0.75rem;">
                <label>Residential Address *</label>
                <textarea name="address" class="form-control" rows="2" required placeholder="P.O. Box 100, Nairobi"></textarea>
            </div>
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label>Enrollment Date</label>
                <input type="date" name="enrollment_date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; gap: 0.5rem;">
                <?= lucide('plus', '', 16) ?> Save Student Record
            </button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
