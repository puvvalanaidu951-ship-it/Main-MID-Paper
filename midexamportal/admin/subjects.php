<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['admin']);
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');
$editSubject = null;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect(base_url('/admin/subjects.php'));
    }

    if (isset($_POST['save_subject'])) {
        $subjectId = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
        $code = strtoupper(sanitize_text($_POST['code'] ?? ''));
        $name = sanitize_text($_POST['name'] ?? '');
        $credits = (int)($_POST['credits'] ?? 0);
        $semester = (int)($_POST['semester'] ?? 0);
        $academicYear = sanitize_text($_POST['academic_year'] ?? '');
        $regulation = sanitize_text($_POST['regulation'] ?? '');
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $facultyId = (int)($_POST['faculty_id'] ?? 0);

        if ($code === '' || $name === '' || $credits === 0 || $semester === 0 || $departmentId === 0) {
            set_flash('warning', 'All required fields must be completed.');
            redirect(base_url('/admin/subjects.php'));
        }

        if ($subjectId > 0) {
            $stmt = $pdo->prepare('UPDATE subjects SET code = :code, name = :name, credits = :credits, semester = :semester, academic_year = :academic_year, regulation = :regulation, department_id = :department_id, faculty_id = :faculty_id WHERE id = :id');
            $stmt->execute([
                ':code' => $code,
                ':name' => $name,
                ':credits' => $credits,
                ':semester' => $semester,
                ':academic_year' => $academicYear,
                ':regulation' => $regulation,
                ':department_id' => $departmentId,
                ':faculty_id' => $facultyId ?: null,
                ':id' => $subjectId,
            ]);
            set_flash('success', 'Subject updated successfully.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO subjects (code, name, credits, semester, academic_year, regulation, department_id, faculty_id) VALUES (:code, :name, :credits, :semester, :academic_year, :regulation, :department_id, :faculty_id)');
            $stmt->execute([
                ':code' => $code,
                ':name' => $name,
                ':credits' => $credits,
                ':semester' => $semester,
                ':academic_year' => $academicYear,
                ':regulation' => $regulation,
                ':department_id' => $departmentId,
                ':faculty_id' => $facultyId ?: null,
            ]);
            set_flash('success', 'Subject added successfully.');
        }
        redirect(base_url('/admin/subjects.php'));
    }

    if (isset($_POST['delete_subject'])) {
        $subjectId = (int)$_POST['subject_id'];
        $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = :id');
        $stmt->execute([':id' => $subjectId]);
        set_flash('success', 'Subject removed successfully.');
        redirect(base_url('/admin/subjects.php'));
    }
}

if ($search !== '') {
    $query = 'SELECT s.*, d.name AS department_name, u.name AS faculty_name FROM subjects s LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN users u ON s.faculty_id = u.id WHERE s.code LIKE :search OR s.name LIKE :search ORDER BY s.name';
    $params = [':search' => '%' . $search . '%'];
} else {
    $query = 'SELECT s.*, d.name AS department_name, u.name AS faculty_name FROM subjects s LEFT JOIN departments d ON s.department_id = d.id LEFT JOIN users u ON s.faculty_id = u.id ORDER BY s.name';
    $params = [];
}
$subjectsStmt = $pdo->prepare($query);
$subjectsStmt->execute($params);
$subjects = $subjectsStmt->fetchAll();

$departments = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();
$faculty = $pdo->query('SELECT id, name FROM users WHERE role_id = (SELECT id FROM roles WHERE slug = "faculty") ORDER BY name')->fetchAll();

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $editStmt = $pdo->prepare('SELECT * FROM subjects WHERE id = :id LIMIT 1');
    $editStmt->execute([':id' => (int)$_GET['id']]);
    $editSubject = $editStmt->fetch();
}

render_header('Subject Management');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Subject Management</h5>
                <p class="text-muted mb-0">Create and maintain subjects with credits, semester, academic year, regulation and assigned faculty.</p>
            </div>
            <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(base_url('/admin/subjects.php')) ?>">Reset</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search subjects by code or name" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="row gy-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3"><?= $editSubject ? 'Edit Subject' : 'Add Subject' ?></h6>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($editSubject['id'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label">Subject Code</label>
                                <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($editSubject['code'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject Name</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editSubject['name'] ?? '') ?>">
                            </div>
                            <div class="row gy-3">
                                <div class="col-md-6">
                                    <label class="form-label">Credits</label>
                                    <input type="number" min="1" name="credits" class="form-control" required value="<?= htmlspecialchars($editSubject['credits'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semester</label>
                                    <input type="number" min="1" max="8" name="semester" class="form-control" required value="<?= htmlspecialchars($editSubject['semester'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row gy-3 mt-3">
                                <div class="col-md-6">
                                    <label class="form-label">Academic Year</label>
                                    <input type="text" name="academic_year" class="form-control" placeholder="2025-2026" value="<?= htmlspecialchars($editSubject['academic_year'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Regulation</label>
                                    <input type="text" name="regulation" class="form-control" placeholder="R20" value="<?= htmlspecialchars($editSubject['regulation'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3 mt-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select" required>
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= $department['id'] ?>" <?= isset($editSubject['department_id']) && $editSubject['department_id'] == $department['id'] ? 'selected' : '' ?>><?= htmlspecialchars($department['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Faculty Assigned</label>
                                <select name="faculty_id" class="form-select">
                                    <option value="">Select faculty</option>
                                    <?php foreach ($faculty as $teacher): ?>
                                        <option value="<?= $teacher['id'] ?>" <?= isset($editSubject['faculty_id']) && $editSubject['faculty_id'] == $teacher['id'] ? 'selected' : '' ?>><?= htmlspecialchars($teacher['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_subject" class="btn btn-success"><?= $editSubject ? 'Update Subject' : 'Add Subject' ?></button>
                                <?php if ($editSubject): ?>
                                    <a href="<?= htmlspecialchars(base_url('/admin/subjects.php')) ?>" class="btn btn-secondary ms-2">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Faculty</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($subjects)): ?>
                            <tr><td colspan="5" class="text-center py-4">No subjects found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($subjects as $subject): ?>
                            <tr>
                                <td><?= htmlspecialchars($subject['code']) ?></td>
                                <td><?= htmlspecialchars($subject['name']) ?></td>
                                <td><?= htmlspecialchars($subject['department_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($subject['faculty_name'] ?? 'Unassigned') ?></td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars(base_url('/admin/subjects.php?action=edit&id=' . (int)$subject['id'])) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
                                        <button type="submit" name="delete_subject" class="btn btn-sm btn-outline-danger" data-confirm="Delete this subject?">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php render_footer();

