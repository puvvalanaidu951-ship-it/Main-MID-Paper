<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['admin']);
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');
$action = $_GET['action'] ?? '';
$editDepartment = null;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect(base_url('/admin/departments.php'));
    }

    if (isset($_POST['save_department'])) {
        $departmentId = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
        $code = strtoupper(sanitize_text($_POST['code'] ?? ''));
        $name = sanitize_text($_POST['name'] ?? '');

        if ($code === '' || $name === '') {
            set_flash('warning', 'Department code and name are required.');
            redirect(base_url('/admin/departments.php'));
        }

        if ($departmentId > 0) {
            $stmt = $pdo->prepare('UPDATE departments SET code = :code, name = :name WHERE id = :id');
            $stmt->execute([':code' => $code, ':name' => $name, ':id' => $departmentId]);
            set_flash('success', 'Department updated successfully.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO departments (code, name) VALUES (:code, :name)');
            $stmt->execute([':code' => $code, ':name' => $name]);
            set_flash('success', 'Department added successfully.');
        }
        redirect(base_url('/admin/departments.php'));
    }

    if (isset($_POST['delete_department'])) {
        $departmentId = (int)$_POST['department_id'];
        $stmt = $pdo->prepare('DELETE FROM departments WHERE id = :id');
        $stmt->execute([':id' => $departmentId]);
        set_flash('success', 'Department removed successfully.');
        redirect(base_url('/admin/departments.php'));
    }
}

if ($action === 'edit' && !empty($_GET['id'])) {
    $editDepartment = $pdo->prepare('SELECT * FROM departments WHERE id = :id');
    $editDepartment->execute([':id' => (int)$_GET['id']]);
    $editDepartment = $editDepartment->fetch();
}

$query = 'SELECT * FROM departments';
$params = [];
if ($search !== '') {
    $query .= ' WHERE code LIKE :search OR name LIKE :search';
    $params[':search'] = '%' . $search . '%';
}
$query .= ' ORDER BY name ASC';
$departments = $pdo->prepare($query);
$departments->execute($params);
$departments = $departments->fetchAll();

render_header('Department Management');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Department Management</h5>
                <p class="text-muted mb-0">Add, edit, search and remove departments with department code and name.</p>
            </div>
            <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(base_url('/admin/departments.php')) ?>">Reset</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search departments by code or name" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="row gy-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3"><?= $editDepartment ? 'Edit Department' : 'Add Department' ?></h6>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="department_id" value="<?= htmlspecialchars($editDepartment['id'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label">Department Code</label>
                                <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($editDepartment['code'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department Name</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editDepartment['name'] ?? '') ?>">
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_department" class="btn btn-success"><?= $editDepartment ? 'Update Department' : 'Add Department' ?></button>
                                <?php if ($editDepartment): ?>
                                    <a href="<?= htmlspecialchars(base_url('/admin/departments.php')) ?>" class="btn btn-secondary ms-2">Cancel</a>
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
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($departments)): ?>
                            <tr><td colspan="3" class="text-center py-4">No departments found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($departments as $department): ?>
                            <tr>
                                <td><?= htmlspecialchars($department['code']) ?></td>
                                <td><?= htmlspecialchars($department['name']) ?></td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars(base_url('/admin/departments.php?action=edit&id=' . (int)$department['id'])) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="department_id" value="<?= $department['id'] ?>">
                                        <button type="submit" name="delete_department" class="btn btn-sm btn-outline-danger" data-confirm="Delete this department?">Delete</button>
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

