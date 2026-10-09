<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['admin']);
$pdo = Database::connect();
$search = sanitize_text($_GET['search'] ?? '');
$editUser = null;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? '';
    if (!csrf_check($token)) {
        set_flash('danger', 'Invalid CSRF token.');
        redirect(base_url('/admin/faculty.php'));
    }

    if (isset($_POST['save_user'])) {
        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $name = sanitize_text($_POST['name'] ?? '');
        $email = sanitize_text($_POST['email'] ?? '');
        $phone = sanitize_text($_POST['phone'] ?? '');
        $employeeId = sanitize_text($_POST['employee_id'] ?? '');
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $designation = sanitize_text($_POST['designation'] ?? '');
        $roleSlug = sanitize_text($_POST['role_slug'] ?? 'faculty');

        if ($name === '' || $email === '') {
            set_flash('warning', 'Name and email are required.');
            redirect(base_url('/admin/faculty.php'));
        }

        $roleStmt = $pdo->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $roleStmt->execute([':slug' => $roleSlug]);
        $role = $roleStmt->fetchColumn();
        if (!$role) {
            set_flash('danger', 'Invalid role selected.');
            redirect(base_url('/admin/faculty.php'));
        }

        if ($userId > 0) {
            $stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, phone = :phone, employee_id = :employee_id, department_id = :department_id, designation = :designation, role_id = :role_id WHERE id = :id');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':employee_id' => $employeeId,
                ':department_id' => $departmentId ?: null,
                ':designation' => $designation,
                ':role_id' => $role,
                ':id' => $userId,
            ]);
            set_flash('success', 'Faculty member updated successfully.');
        } else {
            $passwordHash = password_hash('Admin@123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (role_id, employee_id, name, email, phone, department_id, designation, password) VALUES (:role_id, :employee_id, :name, :email, :phone, :department_id, :designation, :password)');
            $stmt->execute([
                ':role_id' => $role,
                ':employee_id' => $employeeId,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':department_id' => $departmentId ?: null,
                ':designation' => $designation,
                ':password' => $passwordHash,
            ]);
            set_flash('success', 'Faculty member added successfully.');
        }
        redirect(base_url('/admin/faculty.php'));
    }

    if (isset($_POST['delete_user'])) {
        $userId = (int)$_POST['user_id'];
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $userId]);
        set_flash('success', 'Faculty member removed successfully.');
        redirect(base_url('/admin/faculty.php'));
    }
}

if ($search !== '') {
    $query = 'SELECT u.*, r.slug AS role_slug, r.name AS role_name, d.name AS department_name FROM users u LEFT JOIN roles r ON u.role_id = r.id LEFT JOIN departments d ON u.department_id = d.id WHERE u.name LIKE :search OR u.email LIKE :search OR u.employee_id LIKE :search ORDER BY u.name';
    $params = [':search' => '%' . $search . '%'];
} else {
    $query = 'SELECT u.*, r.slug AS role_slug, r.name AS role_name, d.name AS department_name FROM users u LEFT JOIN roles r ON u.role_id = r.id LEFT JOIN departments d ON u.department_id = d.id ORDER BY u.name';
    $params = [];
}
$staffStmt = $pdo->prepare($query);
$staffStmt->execute($params);
$staff = $staffStmt->fetchAll();

$roles = $pdo->query('SELECT slug, name FROM roles ORDER BY name')->fetchAll();
$departments = $pdo->query('SELECT id, name FROM departments ORDER BY name')->fetchAll();

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'edit') {
    $editStmt = $pdo->prepare('SELECT u.*, r.slug AS role_slug FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id LIMIT 1');
    $editStmt->execute([':id' => (int)$_GET['id']]);
    $editUser = $editStmt->fetch();
}

render_header('Faculty Management');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Faculty & Staff Management</h5>
                <p class="text-muted mb-0">Manage employee records, user roles, department assignments and active status.</p>
            </div>
            <a class="btn btn-sm btn-primary" href="<?= htmlspecialchars(base_url('/admin/faculty.php')) ?>">Reset</a>
        </div>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 mb-4">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email, employee ID" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-4">
                <button class="btn btn-outline-primary w-100">Search</button>
            </div>
        </form>

        <div class="row gy-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3"><?= $editUser ? 'Edit User' : 'Add User' ?></h6>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($editUser['id'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editUser['name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" value="<?= htmlspecialchars($editUser['employee_id'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editUser['phone'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">Select department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= $department['id'] ?>" <?= isset($editUser['department_id']) && $editUser['department_id'] == $department['id'] ? 'selected' : '' ?>><?= htmlspecialchars($department['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($editUser['designation'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role_slug" class="form-select" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= htmlspecialchars($role['slug']) ?>" <?= isset($editUser['role_slug']) && $editUser['role_slug'] === $role['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($role['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="save_user" class="btn btn-success"><?= $editUser ? 'Update User' : 'Create User' ?></button>
                                <?php if ($editUser): ?>
                                    <a href="<?= htmlspecialchars(base_url('/admin/faculty.php')) ?>" class="btn btn-secondary ms-2">Cancel</a>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($staff)): ?>
                            <tr><td colspan="5" class="text-center py-4">No users found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($staff as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['role_name']) ?></td>
                                <td><?= htmlspecialchars($row['department_name'] ?? 'N/A') ?></td>
                                <td class="text-end">
                                    <a href="<?= htmlspecialchars(base_url('/admin/faculty.php?action=edit&id=' . (int)$row['id'])) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger" data-confirm="Delete this user?">Delete</button>
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

