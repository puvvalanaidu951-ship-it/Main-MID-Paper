<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();

$paperId = (int)($_GET['id'] ?? 0);
if ($paperId <= 0) {
    http_response_code(400);
    echo 'Invalid paper request.';
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM generated_papers WHERE id = :id AND faculty_id = :faculty_id LIMIT 1');
$stmt->execute([':id' => $paperId, ':faculty_id' => $user['id']]);
$paper = $stmt->fetch();

if (!$paper) {
    http_response_code(404);
    echo 'Paper not found or access denied.';
    exit;
}

$filePath = __DIR__ . '/../' . $paper['file_path'];
if (!file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$pdo->prepare('UPDATE generated_papers SET download_count = download_count + 1 WHERE id = :id')->execute([':id' => $paperId]);
$pdo->prepare('INSERT INTO downloads (generated_paper_id, user_id, ip_address, user_agent) VALUES (:paper_id, :user_id, :ip_address, :user_agent)')
    ->execute([
        ':paper_id' => $paperId,
        ':user_id' => $user['id'],
        ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ]);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
