<?php
// =====================================================================
// Database Connection Configuration (Universal Auto-Connecting PDO)
// Automatically finds, connects, and auto-provisions university_sims
// on XAMPP (Port 3306), Linux MySQL sockets, or local MariaDB.
// =====================================================================

$db_name = 'university_sims';
$db_user = 'root';
$db_pass = ''; // Default XAMPP has empty password

$pdo = null;
$db_connection_info = '';
$connection_errors = [];

// Allow user-override config via query string or session if needed
if (isset($_GET['db_pass'])) {
    $db_pass = $_GET['db_pass'];
}

// Candidates to try in order of preference (XAMPP first, then local fallbacks)
$candidates = [
    // 1. Standard XAMPP localhost socket / default driver
    ['type' => 'XAMPP localhost', 'dsn' => "mysql:host=localhost;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:host=localhost;charset=utf8mb4"],
    
    // 2. Standard XAMPP TCP port 3306 (empty password)
    ['type' => 'XAMPP 127.0.0.1:3306', 'dsn' => "mysql:host=127.0.0.1;port=3306;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:host=127.0.0.1;port=3306;charset=utf8mb4"],
    
    // 3. XAMPP Linux standard socket
    ['type' => 'XAMPP /opt/lampp socket', 'dsn' => "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:unix_socket=/opt/lampp/var/mysql/mysql.sock;charset=utf8mb4"],
    
    // 4. Linux standard socket
    ['type' => 'Linux /run/mysqld socket', 'dsn' => "mysql:unix_socket=/run/mysqld/mysqld.sock;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:unix_socket=/run/mysqld/mysqld.sock;charset=utf8mb4"],
    ['type' => 'Linux /var/run/mysqld socket', 'dsn' => "mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8mb4"],
    ['type' => 'Linux /tmp/mysql socket', 'dsn' => "mysql:unix_socket=/tmp/mysql.sock;dbname={$db_name};charset=utf8mb4", 'user' => $db_user, 'pass' => $db_pass, 'raw_dsn' => "mysql:unix_socket=/tmp/mysql.sock;charset=utf8mb4"],
    
    // 5. Local Sandbox MariaDB socket
    ['type' => 'Local MariaDB (/tmp/mariadb_sims.sock)', 'dsn' => "mysql:unix_socket=/tmp/mariadb_sims.sock;dbname={$db_name};charset=utf8mb4", 'user' => 'root', 'pass' => '', 'raw_dsn' => "mysql:unix_socket=/tmp/mariadb_sims.sock;charset=utf8mb4"],
    
    // 6. Local Sandbox port 3307
    ['type' => 'Local Port 3307', 'dsn' => "mysql:host=127.0.0.1;port=3307;dbname={$db_name};charset=utf8mb4", 'user' => 'root', 'pass' => '', 'raw_dsn' => "mysql:host=127.0.0.1;port=3307;charset=utf8mb4"],
    
    // 7. XAMPP with password 'root'
    ['type' => 'XAMPP 127.0.0.1:3306 (pass=root)', 'dsn' => "mysql:host=127.0.0.1;port=3306;dbname={$db_name};charset=utf8mb4", 'user' => 'root', 'pass' => 'root', 'raw_dsn' => "mysql:host=127.0.0.1;port=3306;charset=utf8mb4"],
];

foreach ($candidates as $c) {
    // If DSN specifies unix_socket and file doesn't exist, skip immediately
    if (strpos($c['dsn'], 'unix_socket=') !== false) {
        preg_match('/unix_socket=([^;]+)/', $c['dsn'], $matches);
        if (!empty($matches[1]) && !file_exists($matches[1])) {
            continue;
        }
    }

    try {
        $pdo = new PDO($c['dsn'], $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT            => 1,
        ]);
        $db_connection_info = $c['type'];
        break;
    } catch (PDOException $e) {
        $connection_errors[$c['type']] = $e->getMessage();
        
        // If the error is 1049 (Unknown database 'university_sims'), MySQL IS running! Auto-create the DB!
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $raw_pdo = new PDO($c['raw_dsn'], $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);
                $raw_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Now connect to the newly created database
                $pdo = new PDO($c['dsn'], $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $db_connection_info = $c['type'] . ' (Auto-Created DB)';
                
                // Import schema and initial seeds automatically
                $setup_sql_file = __DIR__ . '/university_sims_full_setup.sql';
                if (file_exists($setup_sql_file)) {
                    $sql_content = file_get_contents($setup_sql_file);
                    // Split commands by semicolon when not in triggers/procedures or execute via multi-query
                    $pdo->exec("USE `{$db_name}`;");
                    $pdo->exec($sql_content);
                }
                break;
            } catch (Exception $create_err) {
                $connection_errors[$c['type'] . ' (auto-create)'] = $create_err->getMessage();
            }
        }
    }
}

// If no connection succeeded, render a helpful diagnostic screen
if (!$pdo) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Database Connection Required - UniSIMS</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 2rem; color: #0f172a; }
            .diag-card { max-width: 750px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
            .diag-title { font-size: 1.5rem; font-weight: 700; color: #dc2626; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }
            .step-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 8px; margin: 1.25rem 0; }
            .step-box ol { margin-left: 1.25rem; font-size: 0.95rem; line-height: 1.8; }
            .code-pill { background: #0f172a; color: #e2e8f0; padding: 0.2rem 0.5rem; border-radius: 4px; font-family: monospace; font-size: 0.85rem; }
        </style>
    </head>
    <body>
        <div class="diag-card">
            <div class="diag-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block; vertical-align:middle;"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Database Connection Assistance
            </div>
            <p style="color: #475569; font-size: 0.95rem;">
                The web frontend is ready, but could not establish a connection to your MySQL server.
            </p>

            <div class="step-box">
                <strong style="color: #166534;">If you are using XAMPP:</strong>
                <ol>
                    <li>Open your <strong>XAMPP Control Panel</strong>.</li>
                    <li>Click <strong>Start</strong> next to <strong>MySQL</strong>.</li>
                    <li>Open <strong><a href="http://localhost/phpmyadmin" target="_blank" style="color: #15803d; font-weight: bold;">http://localhost/phpmyadmin</a></strong>.</li>
                    <li>Click <strong>Import</strong> &rarr; Choose <span class="code-pill">sql/university_sims_full_setup.sql</span> &rarr; Click <strong>Go</strong>.</li>
                    <li>Refresh this page!</li>
                </ol>
            </div>

            <details style="margin-top: 1rem;">
                <summary style="cursor: pointer; color: #3b82f6; font-weight: 600;">View Attempted Connection Errors</summary>
                <div style="background: #f1f5f9; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; font-size: 0.85rem; font-family: monospace;">
                    <?php foreach ($connection_errors as $target => $err): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <strong><?= htmlspecialchars($target) ?>:</strong><br>
                            <span style="color: #b91c1c;"><?= htmlspecialchars($err) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>

            <div style="margin-top: 1.5rem; text-align: center;">
                <a href="<?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'index.php') ?>" class="btn btn-primary" style="padding: 0.6rem 1.5rem; background: #1e3a8a; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg> Retry Database Connection
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
