<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['admin']);
$pdo = Database::connect();

$facultyStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role_id = (SELECT id FROM roles WHERE slug = :slug)');
$facultyStmt->execute([':slug' => 'faculty']);
$facultyCount = $facultyStmt->fetchColumn();

$stats = [
    'departments' => $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn(),
    'subjects' => $pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn(),
    'faculty' => (int)$facultyCount,
    'questions' => $pdo->query('SELECT COUNT(*) FROM question_bank')->fetchColumn(),
    'approved' => $pdo->query('SELECT COUNT(*) FROM question_bank WHERE status = "approved"')->fetchColumn(),
    'pending' => $pdo->query('SELECT COUNT(*) FROM question_bank WHERE status = "pending"')->fetchColumn(),
    'papers' => $pdo->query('SELECT COUNT(*) FROM generated_papers')->fetchColumn(),
    'today_papers' => $pdo->query('SELECT COUNT(*) FROM generated_papers WHERE DATE(generated_at) = CURDATE()')->fetchColumn(),
];

$recentActivityStmt = $pdo->prepare('SELECT al.created_at, u.name AS user_name, al.action, al.description FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 6');
$recentActivityStmt->execute();
$activities = $recentActivityStmt->fetchAll();

render_header('Admin Dashboard');
?>
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Admin Dashboard</h1>
                <p class="text-muted">Live analytics for departments, subjects, faculty, questions and paper generation.</p>
            </div>
        </div>
    </div>
    <?php foreach ([
        ['label' => 'Total Departments', 'value' => $stats['departments'], 'class' => 'primary'],
        ['label' => 'Total Subjects', 'value' => $stats['subjects'], 'class' => 'success'],
        ['label' => 'Faculty Count', 'value' => $stats['faculty'], 'class' => 'info'],
        ['label' => 'Total Questions', 'value' => $stats['questions'], 'class' => 'warning'],
        ['label' => 'Approved Questions', 'value' => $stats['approved'], 'class' => 'success'],
        ['label' => 'Pending Questions', 'value' => $stats['pending'], 'class' => 'danger'],
        ['label' => 'Generated Papers', 'value' => $stats['papers'], 'class' => 'secondary'],
        ['label' => 'Today\'s Papers', 'value' => $stats['today_papers'], 'class' => 'dark'],
    ] as $card): ?>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-uppercase text-muted"><?= htmlspecialchars($card['label']) ?></small>
                            <h3 class="mt-2 mb-0"><?= htmlspecialchars($card['value']) ?></h3>
                        </div>
                        <div class="badge bg-<?= htmlspecialchars($card['class']) ?> rounded-pill">Live</div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="mb-0">Recent Audit Activity</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?= htmlspecialchars($activity['created_at']) ?></td>
                                <td><?= htmlspecialchars($activity['user_name'] ?: 'System') ?></td>
                                <td><?= htmlspecialchars($activity['action']) ?></td>
                                <td><?= htmlspecialchars($activity['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($activities)): ?>
                            <tr><td colspan="4" class="text-center py-4">No recent activity available.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5>System Summary</h5>
                <p class="text-muted">This dashboard provides administrator access to the full examination management system. Use the left navigation for module operations, approval workflows, and report generation.</p>
            </div>
        </div>
    </div>
</div>
<?php render_footer();
