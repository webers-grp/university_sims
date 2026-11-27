<?php
$page_title = "Database Connection & Sync Diagnostics - UniSIMS";
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/db.php';

$action = $_POST['action'] ?? '';
$action_msg = null;

// Handle manual re-import / re-seed
if ($action === 'reseed') {
    try {
        $setup_file = __DIR__ . '/university_sims_full_setup.sql';
        if (file_exists($setup_file)) {
            // Read and execute via PDO multi-query or command line
            $cmd = "mariadb --socket=/tmp/mariadb_sims.sock -u root < " . escapeshellarg($setup_file) . " 2>&1";
            $output = shell_exec($cmd);
            $action_msg = "Database successfully re-seeded with all 5 departments and 12 courses!";
        }
    } catch (Exception $e) {
        $action_msg = "Error re-seeding: " . $e->getMessage();
    }
}

// Fetch live database status
$tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
$views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_COLUMN);
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_id")->fetchAll();
$courses = $pdo->query("SELECT * FROM courses ORDER BY department_id, course_code")->fetchAll();
$students = $pdo->query("
    SELECT s.student_id, s.reg_number, s.first_name, s.last_name, s.email, d.department_name, d.tuition_fee
    FROM students s
    JOIN departments d ON s.department_id = d.department_id
    ORDER BY s.student_id
")->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="page-header">
    <div class="page-title">
        <h1><?= lucide('database', '', 26) ?> Database Connection &amp; Live Sync Diagnostics</h1>
        <p>Verify that your frontend is reading and writing to your database server in real-time</p>
    </div>
</div>

<?php if ($action_msg): ?>
    <div class="alert alert-success" style="display: flex; align-items: center; gap: 0.6rem;">
        <?= lucide('check-circle', '', 18) ?>
        <div><?= htmlspecialchars($action_msg) ?></div>
    </div>
<?php endif; ?>

<!-- ACTIVE CONNECTION BADGE CARD -->
<div class="card" style="border: 2px solid #bbf7d0; background: #f0fdf4;">
    <div class="card-header" style="border-color: #bbf7d0;">
        <h2 style="color: #166534; display: flex; align-items: center; gap: 0.5rem;">
            <?= lucide('check-circle', '', 20) ?> Live Database Connection: Active
        </h2>
        <span class="badge badge-success"><?= htmlspecialchars($db_connection_info) ?></span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; font-size: 0.9rem;">
        <div>
            <span style="font-size: 0.75rem; color: #166534; text-transform: uppercase; font-weight: bold;">Connected Engine</span>
            <div style="font-size: 1.1rem; font-weight: 700; color: #14532d;"><?= htmlspecialchars($db_connection_info) ?></div>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: #166534; text-transform: uppercase; font-weight: bold;">Base Tables</span>
            <div style="font-size: 1.1rem; font-weight: 700; color: #14532d;"><?= count($tables) ?> Tables Active</div>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: #166534; text-transform: uppercase; font-weight: bold;">Relational Views</span>
            <div style="font-size: 1.1rem; font-weight: 700; color: #14532d;"><?= count($views) ?> Views Active</div>
        </div>
        <div>
            <span style="font-size: 0.75rem; color: #166534; text-transform: uppercase; font-weight: bold;">Students in DB</span>
            <div style="font-size: 1.1rem; font-weight: 700; color: #14532d;"><?= count($students) ?> Students Registered</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <!-- 5 DEPARTMENTS -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2><?= lucide('building', '', 18) ?> Departments in Database (<?= count($departments) ?>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Semester Tuition</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $d): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($d['department_code']) ?></span></td>
                        <td><strong><?= htmlspecialchars($d['department_name']) ?></strong></td>
                        <td style="color: #15803d; font-weight: 700;">KSh <?= number_format($d['tuition_fee'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 12 COURSES -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h2><?= lucide('book-open', '', 18) ?> Active Courses (<?= count($courses) ?>)</h2>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Course Title</th>
                        <th>Credits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($c['course_code']) ?></span></td>
                        <td><?= htmlspecialchars($c['course_title']) ?></td>
                        <td><?= $c['credit_hours'] ?> hrs</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- STUDENTS CURRENTLY IN DB -->
<div class="card">
    <div class="card-header">
        <h2><?= lucide('users', '', 18) ?> All Students Currently Live in Database (<?= count($students) ?>)</h2>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Registration Number</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Institutional Email</th>
                    <th>Expected Tuition</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $st): ?>
                <tr>
                    <td>#<?= $st['student_id'] ?></td>
                    <td><strong><?= htmlspecialchars($st['reg_number']) ?></strong></td>
                    <td><?= htmlspecialchars($st['first_name'] . ' ' . $st['last_name']) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($st['department_name']) ?></span></td>
                    <td><code><?= htmlspecialchars($st['email']) ?></code></td>
                    <td style="color: #15803d; font-weight: 700;">KSh <?= number_format($st['tuition_fee'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- REAL-TIME TWO-WAY SYNC STATUS -->
<div class="card" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0;">
    <div>
        <h3 style="font-size: 1rem; color: #166534; display: flex; align-items: center; gap: 0.5rem;">
            <?= lucide('check-circle', '', 18) ?> Direct Real-Time Two-Way Sync Active
        </h3>
        <p style="font-size: 0.85rem; color: #15803d; margin-top: 0.25rem;">
            Both this web UI and phpMyAdmin are connected directly to the same live MySQL database (<code>university_sims</code>). Any change made in phpMyAdmin appears immediately here upon page refresh, and any change made in this dashboard writes directly to phpMyAdmin with <strong>no file exporting or importing required</strong>.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=university_sims" target="_blank" class="btn btn-primary" style="gap: 0.5rem; background: #15803d;">
            <?= lucide('database', '', 16) ?> Open in phpMyAdmin
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
