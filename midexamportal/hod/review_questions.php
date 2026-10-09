<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['hod']);
$user = current_user();
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid request token.');
        redirect(base_url('/hod/review_questions.php'));
    }

    $questionId = (int)($_POST['question_id'] ?? 0);
    $status = sanitize_text($_POST['status'] ?? 'pending');
    $remarks = sanitize_text($_POST['remarks'] ?? '');

    if ($questionId > 0 && in_array($status, ['approved', 'rejected'], true)) {
        $stmt = $pdo->prepare('UPDATE question_bank SET status = :status, remarks = :remarks WHERE id = :id');
        $stmt->execute([':status' => $status, ':remarks' => $remarks, ':id' => $questionId]);
        require_once __DIR__ . '/../includes/AuditLog.php';
        require_once __DIR__ . '/../includes/Notification.php';

        $stmt = $pdo->prepare('SELECT faculty_id FROM question_bank WHERE id = :id');
        $stmt->execute([':id' => $questionId]);
        $facultyId = (int)$stmt->fetchColumn();

        AuditLog::record($pdo, $user['id'], 'review_question', 'HOD reviewed a question and set status to ' . $status, 'question_bank', $questionId);
        Notification::send($pdo, $facultyId, 'Your question has been ' . $status . ' by HOD. Remarks: ' . ($remarks ?: 'No remarks')); 
        set_flash('success', 'Question status updated.');
    }
    redirect(base_url('/hod/review_questions.php'));
}

$query = 'SELECT q.*, s.name AS subject_name, u.name AS unit_name, t.name AS question_type, us.name AS faculty_name FROM question_bank q JOIN subjects s ON q.subject_id = s.id JOIN question_units u ON q.unit_id = u.id JOIN question_types t ON q.question_type_id = t.id JOIN users us ON q.faculty_id = us.id WHERE q.status = "pending"';
$params = [];
if ($search !== '') {
    $query .= ' AND (q.question_text LIKE :search OR q.co_mapping LIKE :search OR q.bloom_level LIKE :search OR us.name LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}
$query .= ' ORDER BY q.created_at ASC';
$questions = $pdo->prepare($query);
$questions->execute($params);
$questions = $questions->fetchAll();

render_header('Review Questions');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">HOD Question Review</h5>
            <p class="text-muted mb-0">Review pending faculty questions and approve or reject with remarks.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search pending questions, CO, Bloom or faculty" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Faculty</th>
                    <th>Subject</th>
                    <th>Unit</th>
                    <th>Type</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($questions)): ?>
                    <tr><td colspan="6" class="text-center py-4">No pending questions for review.</td></tr>
                <?php endif; ?>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?= htmlspecialchars($question['created_at']) ?></td>
                        <td><?= htmlspecialchars($question['faculty_name']) ?></td>
                        <td><?= htmlspecialchars($question['subject_name']) ?></td>
                        <td><?= htmlspecialchars($question['unit_name']) ?></td>
                        <td><?= htmlspecialchars($question['question_type']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $question['id'] ?>">Review</button>
                        </td>
                    </tr>
                    <div class="modal fade" id="reviewModal<?= $question['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Review Question</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Question:</strong></p>
                                    <div class="border rounded p-3 mb-3"><?= nl2br(htmlspecialchars($question['question_text'])) ?></div>
                                    <?php if ($question['image_path']): ?>
                                        <div class="mb-3"><img src="<?= htmlspecialchars($question['image_path']) ?>" class="img-fluid rounded" alt="Question diagram"></div>
                                    <?php endif; ?>
                                    <ul class="list-group mb-3">
                                        <li class="list-group-item"><strong>CO Mapping:</strong> <?= htmlspecialchars($question['co_mapping']) ?></li>
                                        <li class="list-group-item"><strong>Bloom's Level:</strong> <?= htmlspecialchars($question['bloom_level']) ?></li>
                                        <li class="list-group-item"><strong>Difficulty:</strong> <?= htmlspecialchars($question['difficulty']) ?></li>
                                        <li class="list-group-item"><strong>Marks:</strong> <?= htmlspecialchars($question['marks']) ?></li>
                                    </ul>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="approved">Approve</option>
                                                <option value="rejected">Reject</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea name="remarks" rows="3" class="form-control" placeholder="Optional feedback for faculty"></textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success">Save Decision</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer();

