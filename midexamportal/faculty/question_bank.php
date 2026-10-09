<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');
$units = $pdo->query('SELECT id, name FROM question_units ORDER BY id')->fetchAll();
$types = $pdo->query('SELECT id, name FROM question_types ORDER BY id')->fetchAll();
$subjects = $pdo->prepare('SELECT id, name FROM subjects WHERE faculty_id = :faculty_id OR department_id = :department_id ORDER BY name');
$subjects->execute([':faculty_id' => $user['id'], ':department_id' => $user['department_id']]);
$subjects = $subjects->fetchAll();

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid request token.');
        redirect(base_url('/faculty/question_bank.php'));
    }

    $subjectId = (int)($_POST['subject_id'] ?? 0);
    $unitId = (int)($_POST['unit_id'] ?? 0);
    $typeId = (int)($_POST['question_type_id'] ?? 0);
    $questionText = trim($_POST['question_text'] ?? '');
    $coMapping = sanitize_text($_POST['co_mapping'] ?? '');
    $bloomLevel = sanitize_text($_POST['bloom_level'] ?? '');
    $difficulty = sanitize_text($_POST['difficulty'] ?? 'Medium');
    $marks = (int)($_POST['marks'] ?? 0);
    $status = 'pending';
    $objectiveOptions = null;
    $imagePath = null;

    if ($subjectId === 0 || $unitId === 0 || $typeId === 0 || $questionText === '' || $coMapping === '' || $bloomLevel === '' || $marks === 0) {
        set_flash('warning', 'Please complete all required question fields.');
        redirect(base_url('/faculty/question_bank.php'));
    }

    if (!empty($_FILES['question_image']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/questions/';
        $imageFile = basename($_FILES['question_image']['name']);
        $targetFile = $uploadDir . time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $imageFile);
        if (move_uploaded_file($_FILES['question_image']['tmp_name'], $targetFile)) {
            $imagePath = base_url('uploads/questions/' . basename($targetFile));
        }
    }

    if ($typeId === 1) {
        $options = array_map('trim', [$_POST['option_a'] ?? '', $_POST['option_b'] ?? '', $_POST['option_c'] ?? '', $_POST['option_d'] ?? '']);
        $answer = sanitize_text($_POST['answer'] ?? '');
        if (empty($options[0]) || empty($options[1]) || empty($answer)) {
            set_flash('warning', 'Objective questions require options and an answer.');
            redirect(base_url('/faculty/question_bank.php'));
        }
        $objectiveOptions = json_encode(['choices' => $options, 'answer' => $answer], JSON_UNESCAPED_UNICODE);
    }

    $duplicateStmt = $pdo->prepare('SELECT COUNT(*) FROM question_bank WHERE subject_id = :subject_id AND unit_id = :unit_id AND question_text LIKE :text');
    $duplicateStmt->execute([':subject_id' => $subjectId, ':unit_id' => $unitId, ':text' => '%' . substr($questionText, 0, 120) . '%']);
    $duplicateCount = (int)$duplicateStmt->fetchColumn();
    if ($duplicateCount > 0) {
        set_flash('warning', 'A similar question already exists in the question bank. Please review duplicates before uploading.');
        redirect(base_url('/faculty/question_bank.php'));
    }

    $stmt = $pdo->prepare('INSERT INTO question_bank (subject_id, department_id, faculty_id, question_text, image_path, question_type_id, objective_options, marks, unit_id, co_mapping, bloom_level, difficulty, status) VALUES (:subject_id, :department_id, :faculty_id, :question_text, :image_path, :question_type_id, :objective_options, :marks, :unit_id, :co_mapping, :bloom_level, :difficulty, :status)');
    $stmt->execute([
        ':subject_id' => $subjectId,
        ':department_id' => (int)$user['department_id'],
        ':faculty_id' => $user['id'],
        ':question_text' => $questionText,
        ':image_path' => $imagePath,
        ':question_type_id' => $typeId,
        ':objective_options' => $objectiveOptions,
        ':marks' => $marks,
        ':unit_id' => $unitId,
        ':co_mapping' => $coMapping,
        ':bloom_level' => $bloomLevel,
        ':difficulty' => $difficulty,
        ':status' => $status,
    ]);

    require_once __DIR__ . '/../includes/AuditLog.php';
    AuditLog::record($pdo, $user['id'], 'upload_question', 'Faculty uploaded a new question for HOD review.', 'question_bank', (int)$pdo->lastInsertId());

    set_flash('success', 'Question uploaded successfully and submitted for HOD review.');
    redirect(base_url('/faculty/question_bank.php'));
}

$query = 'SELECT q.*, 
                 s.name AS subject_name, 
                 u.name AS unit_name, 
                 t.name AS question_type
          FROM question_bank q
          JOIN subjects s ON q.subject_id = s.id
          JOIN question_units u ON q.unit_id = u.id
          JOIN question_types t ON q.question_type_id = t.id
          WHERE q.faculty_id = :faculty_id';

$params = [
    ':faculty_id' => $user['id']
];

if ($search !== '') {
    $query .= ' AND (
        q.question_text LIKE :search_question
        OR q.co_mapping LIKE :search_co
        OR q.bloom_level LIKE :search_bloom
        OR q.difficulty LIKE :search_difficulty
    )';

    $searchValue = '%' . $search . '%';

    $params[':search_question'] = $searchValue;
    $params[':search_co'] = $searchValue;
    $params[':search_bloom'] = $searchValue;
    $params[':search_difficulty'] = $searchValue;
}

$query .= ' ORDER BY q.created_at DESC';

$questions = $pdo->prepare($query);
$questions->execute($params);
$questions = $questions->fetchAll();

render_header('Question Bank');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Question Bank</h5>
            <p class="text-muted mb-0">Upload and manage questions by unit, subject, CO mapping, Bloom's level and difficulty.</p>
        </div>
        <span class="badge bg-warning text-dark">Pending approval workflow</span>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search your questions by keyword, CO, Bloom or difficulty" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">Upload New Question</h6>
                <form method="post" enctype="multipart/form-data">
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
                            <label class="form-label">Question Type</label>
                            <select name="question_type_id" class="form-select" required>
                                <option value="">Select type</option>
                                <?php foreach ($types as $type): ?>
                                    <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">Select unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>"><?= htmlspecialchars($unit['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Marks</label>
                            <input type="number" min="1" max="12" name="marks" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CO Mapping</label>
                            <input type="text" name="co_mapping" class="form-control" placeholder="CO1, CO2" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bloom's Level</label>
                            <select name="bloom_level" class="form-select" required>
                                <option value="">Select level</option>
                                <option value="Remember">Remember</option>
                                <option value="Understand">Understand</option>
                                <option value="Apply">Apply</option>
                                <option value="Analyze">Analyze</option>
                                <option value="Evaluate">Evaluate</option>
                                <option value="Create">Create</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Difficulty</label>
                            <select name="difficulty" class="form-select" required>
                                <option value="Easy">Easy</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="Hard">Hard</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Question Text</label>
                            <textarea name="question_text" rows="4" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Upload Diagram / Image</label>
                            <input type="file" name="question_image" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Objective Options (for MCQ only)</label>
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="option_a" class="form-control" placeholder="Option A"></div>
                                <div class="col-6"><input type="text" name="option_b" class="form-control" placeholder="Option B"></div>
                                <div class="col-6"><input type="text" name="option_c" class="form-control" placeholder="Option C"></div>
                                <div class="col-6"><input type="text" name="option_d" class="form-control" placeholder="Option D"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correct Answer</label>
                            <input type="text" name="answer" class="form-control" placeholder="E.g. A or B">
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-success">Upload Question</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Unit</th>
                    <th>Type</th>
                    <th>Marks</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($questions)): ?>
                    <tr><td colspan="6" class="text-center py-4">No question history yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?= htmlspecialchars($question['created_at']) ?></td>
                        <td><?= htmlspecialchars($question['subject_name']) ?></td>
                        <td><?= htmlspecialchars($question['unit_name']) ?></td>
                        <td><?= htmlspecialchars($question['question_type']) ?></td>
                        <td><?= htmlspecialchars($question['marks']) ?></td>
                        <td><span class="badge bg-<?= $question['status'] === 'approved' ? 'success' : ($question['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= htmlspecialchars(ucfirst($question['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer();

