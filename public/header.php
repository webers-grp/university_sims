<?php
// header.php
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/db.php';

if (!isset($page_title)) {
    $page_title = "University Student Information Management System";
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - SIMS Portal</title>
    <link rel="stylesheet" href="style.css">
    <!-- Lucide Icons Script -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <header class="navbar">
        <div class="nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <?= lucide('graduation-cap', '', 24) ?>
                </div>
                <div>
                    <div class="brand-title">UniSIMS Portal</div>
                    <div class="brand-subtitle">Advanced Database Management System</div>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>"><?= lucide('layout-dashboard', '', 16) ?> <span>Dashboard</span></a></li>
                <li><a href="students.php" class="<?= $current_page == 'students.php' ? 'active' : '' ?>"><?= lucide('users', '', 16) ?> <span>Students</span></a></li>
                <li><a href="courses.php" class="<?= $current_page == 'courses.php' ? 'active' : '' ?>"><?= lucide('book-open', '', 16) ?> <span>Courses</span></a></li>
                <li><a href="registrations.php" class="<?= $current_page == 'registrations.php' ? 'active' : '' ?>"><?= lucide('clipboard-check', '', 16) ?> <span>Registrations</span></a></li>
                <li><a href="examinations.php" class="<?= $current_page == 'examinations.php' ? 'active' : '' ?>"><?= lucide('target', '', 16) ?> <span>Examinations</span></a></li>
                <li><a href="fees.php" class="<?= $current_page == 'fees.php' ? 'active' : '' ?>"><?= lucide('credit-card', '', 16) ?> <span>Fee Payments</span></a></li>
                <li><a href="advanced_demo.php" class="special-badge <?= $current_page == 'advanced_demo.php' ? 'active' : '' ?>"><?= lucide('zap', '', 16) ?> <span>ADBMS Lab</span></a></li>
                <li>
                    <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=university_sims" target="_blank" class="btn btn-sm btn-outline" style="background: rgba(34, 197, 94, 0.2); color: #86efac; border-color: rgba(34, 197, 94, 0.5); padding: 0.4rem 0.75rem; display: inline-flex; align-items: center; gap: 0.4rem;" title="Direct Live Sync with phpMyAdmin Active">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#22c55e; box-shadow:0 0 8px #22c55e;"></span>
                        <span>phpMyAdmin Live Sync</span>
                    </a>
                </li>
            </ul>
        </div>
    </header>
    <main class="main-wrapper">
