<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/AuditLog.php';
require_once __DIR__ . '/../includes/Notification.php';
require_once __DIR__ . '/../includes/PdfGenerator.php';
require_once __DIR__ . '/../includes/MailSender.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();
$subjects = $pdo->prepare('SELECT id, name FROM subjects WHERE faculty_id = :faculty_id ORDER BY name');
$subjects->execute([':faculty_id' => $user['id']]);
$subjects = $subjects->fetchAll();
$units = $pdo->query('SELECT id FROM question_units ORDER BY id')->fetchAll();
$message = '';

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid request token.');
        redirect(base_url('/faculty/generate_paper.php'));
    }

    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $semester = (int)($_POST['semester'] ?? 0);
    $academicYear = sanitize_text($_POST['academic_year'] ?? '');
    $regulation = sanitize_text($_POST['regulation'] ?? '');
    $paperBase = 'PAPER-' . time() . '-' . rand(100, 999);
    $generatedAt = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $browserInfo = $_SERVER['HTTP_USER_AGENT'] ?? null;

    if ($subjectId === 0 || $semester === 0 || $academicYear === '' || $regulation === '') {
        set_flash('warning', 'All paper generation fields are required.');
        redirect(base_url('/faculty/generate_paper.php'));
    }

    $subjectStmt = $pdo->prepare('SELECT name FROM subjects WHERE id = :id LIMIT 1');
    $subjectStmt->execute([':id' => $subjectId]);
    $subjectRow = $subjectStmt->fetch();
    if (!$subjectRow) {
        set_flash('danger', 'Selected subject is invalid.');
        redirect(base_url('/faculty/generate_paper.php'));
    }
    $subjectName = $subjectRow['name'];

    $sixMarkPool = $pdo->prepare('SELECT * FROM question_bank WHERE subject_id = :subject_id AND status = "approved" AND question_type_id = 2');
    $sixMarkPool->execute([':subject_id' => $subjectId]);
    $sixMarkPool = $sixMarkPool->fetchAll();

    $twelveMarkPool = $pdo->prepare('SELECT * FROM question_bank WHERE subject_id = :subject_id AND status = "approved" AND question_type_id = 3');
    $twelveMarkPool->execute([':subject_id' => $subjectId]);
    $twelveMarkPool = $twelveMarkPool->fetchAll();

    $objectivePool = $pdo->prepare('SELECT * FROM question_bank WHERE subject_id = :subject_id AND status = "approved" AND question_type_id = 1');
    $objectivePool->execute([':subject_id' => $subjectId]);
    $objectivePool = $objectivePool->fetchAll();

    if (count($sixMarkPool) < 4 || count($twelveMarkPool) < 2 || count($objectivePool) < 20) {
        set_flash('danger', 'Not enough approved question bank coverage for both paper sets.');
        redirect(base_url('/faculty/generate_paper.php'));
    }

    $setQuestions = function (array $pool, int $count, array $exclude = []): array {
        $available = array_filter($pool, fn($row) => !in_array($row['id'], $exclude, true));
        shuffle($available);
        return array_slice($available, 0, $count);
    };

    $set1Six = $setQuestions($sixMarkPool, 4);
    $set1Twelve = $setQuestions($twelveMarkPool, 2);
    $set1Objective = $setQuestions($objectivePool, 10);

    $set2Six = $setQuestions($sixMarkPool, 4, array_column($set1Six, 'id'));
    $set2Twelve = $setQuestions($twelveMarkPool, 2, array_column($set1Twelve, 'id'));
    $set2Objective = $setQuestions($objectivePool, 10, array_column($set1Objective, 'id'));

    if (count($set2Six) < 4 || count($set2Twelve) < 2 || count($set2Objective) < 10) {
        set_flash('danger', 'Unable to generate two distinct paper sets with current approved questions. Add more questions or adjust selections.');
        redirect(base_url('/faculty/generate_paper.php'));
    }

    $paperFileName = $paperBase . '.pdf';
    $paperRelativePath = 'uploads/papers/' . $paperFileName;
    $paperFile = __DIR__ . '/../' . $paperRelativePath;
    $paperUrl = base_url($paperRelativePath);

    $renderSection = function (string $title, array $sixItems, array $twelveItems, array $objectiveItems): string {
        $html = '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<h5>Descriptive Questions</h5>';
        $questionCounter = 1;
        foreach ($sixItems as $q) {
            $html .= '<p><strong>' . $questionCounter++ . '. (' . $q['marks'] . ' marks)</strong><br>' . nl2br(htmlspecialchars($q['question_text'])) . '</p>';
        }
        foreach ($twelveItems as $q) {
            $html .= '<p><strong>' . $questionCounter++ . '. (' . $q['marks'] . ' marks)</strong><br>' . nl2br(htmlspecialchars($q['question_text'])) . '</p>';
        }
        $html .= '<h5>Objective Questions</h5><ol>';
        foreach ($objectiveItems as $q) {
            $options = json_decode($q['objective_options'], true);
            $html .= '<li><p>' . nl2br(htmlspecialchars($q['question_text'])) . '</p>';
            if (!empty($options['choices'])) {
                foreach ($options['choices'] as $index => $choice) {
                    $html .= '<div>' . chr(65 + $index) . '. ' . htmlspecialchars($choice) . '</div>';
                }
            }
            $html .= '</li>';
        }
        $html .= '</ol>';
        return $html;
    };

    ob_start();
    ?>
    <html><body>
        <div style="text-align:center; margin-bottom:30px;">
            <h2>College Question Paper</h2>
            <p>Subject: <?= htmlspecialchars($subjectName) ?></p>
            <p>Semester: <?= htmlspecialchars($semester) ?> | Academic Year: <?= htmlspecialchars($academicYear) ?> | Regulation: <?= htmlspecialchars($regulation) ?></p>
        </div>
        <?= $renderSection('SET-I', $set1Six, $set1Twelve, $set1Objective) ?>
        <div style="page-break-after: always;"></div>
        <?= $renderSection('SET-II', $set2Six, $set2Twelve, $set2Objective) ?>
    </body></html>
    <?php
    $html = ob_get_clean();
    $success = PdfGenerator::render($html, $paperFile);

    if (!$success) {
        set_flash('danger', 'Failed to generate paper PDF.');
        redirect(base_url('/faculty/generate_paper.php'));
    }

    $paperPath = $paperRelativePath;
    $sets = ['SET-I', 'SET-II'];
    $generatedIds = [];
    foreach ($sets as $setType) {
        $stmt = $pdo->prepare('INSERT INTO generated_papers (paper_code, subject_id, department_id, faculty_id, semester, academic_year, regulation, generated_at, set_type, status, file_path, ip_address, browser_info) VALUES (:paper_code, :subject_id, :department_id, :faculty_id, :semester, :academic_year, :regulation, :generated_at, :set_type, :status, :file_path, :ip_address, :browser_info)');
        $stmt->execute([
            ':paper_code' => $paperBase . '-' . $setType,
            ':subject_id' => $subjectId,
            ':department_id' => $user['department_id'],
            ':faculty_id' => $user['id'],
            ':semester' => $semester,
            ':academic_year' => $academicYear,
            ':regulation' => $regulation,
            ':generated_at' => $generatedAt,
            ':set_type' => $setType,
            ':status' => 'final',
            ':file_path' => $paperPath,
            ':ip_address' => $ipAddress,
            ':browser_info' => $browserInfo,
        ]);
        $generatedIds[$setType] = (int)$pdo->lastInsertId();
    }

    $insertGenerated = function (int $id, array $questions) use ($pdo) {
        foreach ($questions as $order => $question) {
            $stmt = $pdo->prepare('INSERT INTO generated_questions (generated_paper_id, question_bank_id, question_order) VALUES (:paper_id, :question_id, :question_order)');
            $stmt->execute([':paper_id' => $id, ':question_id' => $question['id'], ':question_order' => $order + 1]);
        }
    };

    $insertGenerated($generatedIds['SET-I'], array_merge($set1Six, $set1Twelve, $set1Objective));
    $insertGenerated($generatedIds['SET-II'], array_merge($set2Six, $set2Twelve, $set2Objective));

    AuditLog::record($pdo, $user['id'], 'generate_paper', 'Generated question papers ' . $paperBase, 'generated_papers', $generatedIds['SET-I']);
    Notification::send($pdo, $user['id'], 'Question paper ' . $paperBase . ' has been generated successfully.');

    $config = require __DIR__ . '/../config/config.php';
    $downloadUrl1 = base_url('faculty/download_paper.php?id=' . $generatedIds['SET-I']);
    $downloadUrl2 = base_url('faculty/download_paper.php?id=' . $generatedIds['SET-II']);
    $mailBody = '<p>Question paper generated successfully.</p>'
        . '<p>Paper ID: ' . htmlspecialchars($paperBase) . '</p>'
        . '<p><a href="' . htmlspecialchars($downloadUrl1) . '">Download SET-I</a></p>'
        . '<p><a href="' . htmlspecialchars($downloadUrl2) . '">Download SET-II</a></p>';
    $sentCount = 0;
    $failedCount = 0;
    $emailLogStmt = $pdo->prepare('INSERT INTO email_logs (generated_paper_id, recipient, subject, body, status, error_message) VALUES (:generated_paper_id, :recipient, :subject, :body, :status, :error_message)');

    foreach ($config['email']['notify_to'] as $recipient) {
        $sent = MailSender::send($recipient, 'Question Paper Generated Successfully', $mailBody);
        $status = $sent ? 'sent' : 'failed';
        $errorMessage = $sent ? null : MailSender::lastError();

        $emailLogStmt->execute([
            ':generated_paper_id' => $generatedIds['SET-I'],
            ':recipient' => $recipient,
            ':subject' => 'Question Paper Generated Successfully',
            ':body' => $mailBody,
            ':status' => $status,
            ':error_message' => $errorMessage,
        ]);

        if ($sent) {
            $sentCount++;
        } else {
            $failedCount++;
        }
    }

    $flashMessage = 'Question paper generated and PDF saved.';
    if ($sentCount > 0) {
        $flashMessage .= ' ' . $sentCount . ' notification email(s) sent.';
    }
    if ($failedCount > 0) {
        $flashMessage .= ' ' . $failedCount . ' email(s) failed to send.';
    }

    set_flash('success', $flashMessage);
    redirect(base_url('/faculty/generate_paper.php'));
}

render_header('Generate Question Paper');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Generate Question Paper</h5>
        <p class="text-muted mb-0">Generate a randomized question paper from approved questions.</p>
        <p class="text-muted mb-0">Please upload (15 theory) and (25 objective) questions before generating the paper.</p>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Semester</label>
                    <input type="number" name="semester" min="1" max="8" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Academic Year</label>
                    <input type="text" name="academic_year" class="form-control" placeholder="2025-2026" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Regulation</label>
                    <input type="text" name="regulation" class="form-control" placeholder="R20" required>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-success">Generate Paper</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php render_footer();

