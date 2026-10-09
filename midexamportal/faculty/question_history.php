<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');

$query = 'SELECT q.*, s.name AS subject_name, u.name AS unit_name, t.name AS question_type, us.name AS faculty_name FROM question_bank q JOIN subjects s ON q.subject_id = s.id JOIN question_units u ON q.unit_id = u.id JOIN question_types t ON q.question_type_id = t.id JOIN users us ON q.faculty_id = us.id WHERE q.faculty_id = :faculty_id';
$params = [':faculty_id' => $user['id']];
if ($search !== '') {
    $query .= ' AND (q.question_text LIKE :search OR q.co_mapping LIKE :search OR q.bloom_level LIKE :search OR q.difficulty LIKE :search OR q.status LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}
$query .= ' ORDER BY q.created_at DESC';
$questions = $pdo->prepare($query);
$questions->execute($params);
$questions = $questions->fetchAll();

render_header('Question History');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">My Question History</h5>
            <p class="text-muted mb-0">Search questions by status, CO, Bloom level, difficulty or keywords.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search questions" value="<?= htmlspecialchars($search) ?>">
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
                    <th>Subject</th>
                    <th>Unit</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Remarks</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($questions)): ?>
                    <tr><td colspan="6" class="text-center py-4">No question history records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?= htmlspecialchars($question['created_at']) ?></td>
                        <td><?= htmlspecialchars($question['subject_name']) ?></td>
                        <td><?= htmlspecialchars($question['unit_name']) ?></td>
                        <td><?= htmlspecialchars($question['question_type']) ?></td>
                        <td><span class="badge bg-<?= $question['status'] === 'approved' ? 'success' : ($question['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucfirst($question['status'])) ?></span></td>
                        <td><?= htmlspecialchars($question['remarks'] ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer();
