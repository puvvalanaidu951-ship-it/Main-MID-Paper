<?php
require_once __DIR__ . '/includes/Database.php';
try {
    $pdo = Database::connect();
    $stmt = $pdo->query("SELECT id, email, role_id, status, created_at FROM users ORDER BY id LIMIT 20");
    foreach ($stmt as $row) {
        echo implode(' | ', $row) . PHP_EOL;
    }
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
