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

    if (in_array('users', $tables)) {
        $users = $pdo->query("SELECT employee_id, email, password, is_active FROM users LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "\nUsers:\n";
        foreach ($users as $u) {
            $pwOk = password_verify('Admin@1234', $u['password']) ? 'Admin@1234 OK' : 'WRONG HASH';
            echo "  {$u['employee_id']} | {$u['email']} | active:{$u['is_active']} | pw: $pwOk\n";
        }
    }
} catch (PDOException $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

// 5. Session test
session_name('VVCRMSS_TEST');
session_start();
$_SESSION['test'] = time();
echo "\nSession: " . (isset($_SESSION['test']) ? 'OK' : 'FAILED') . "\n";
session_destroy();

echo "\nSCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'unknown') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . "\n";

echo "</pre><p style='color:red'><b>DELETE public/check.php after fixing!</b></p>";
