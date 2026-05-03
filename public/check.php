<?php
// Temporary diagnostic — DELETE after setup is complete
define('ROOT', dirname(__DIR__));

echo '<h2>VastuVeda CRM — Setup Check</h2><pre>';

// 1. PHP version
echo "PHP: " . PHP_VERSION . "\n";

// 2. Required extensions
$exts = ['pdo_mysql','mbstring','json','fileinfo','gd','session'];
foreach ($exts as $e) {
    echo "  ext/$e: " . (extension_loaded($e) ? 'OK' : 'MISSING') . "\n";
}

// 3. Database connection
$cfg = require ROOT . '/config/database.php';
echo "\nDB host:   " . $cfg['host'] . "\n";
echo "DB name:   " . $cfg['dbname'] . "\n";
echo "DB user:   " . $cfg['user'] . "\n";
try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4",
        $cfg['user'], $cfg['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "DB connect: OK\n";

    // 4. Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables (" . count($tables) . "): " . implode(', ', $tables) . "\n";

    // 5. Check users
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Users in DB: $count\n";

    if ($count > 0) {
        $user = $pdo->query("SELECT employee_id, email, LEFT(password,7) as pw_prefix, is_active FROM users LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
        echo "Sample users:\n";
        foreach ($user as $u) {
            echo "  {$u['employee_id']} | {$u['email']} | pw_prefix:{$u['pw_prefix']} | active:{$u['is_active']}\n";
        }
    }
} catch (PDOException $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

// 6. Session test
session_name('VVCRMSS_TEST');
session_start();
$_SESSION['test'] = time();
echo "\nSession: " . (isset($_SESSION['test']) ? 'OK' : 'FAILED') . "\n";
echo "Session save path: " . ini_get('session.save_path') . "\n";
session_destroy();

// 7. mod_rewrite
echo "\nSERVER_SOFTWARE: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'unknown') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . "\n";

echo "</pre><p style='color:red'><b>Delete public/check.php after fixing!</b></p>";
