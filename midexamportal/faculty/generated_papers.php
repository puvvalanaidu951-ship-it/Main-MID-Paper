<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();

$query = 'SELECT gp.*, s.name AS subject_name FROM generated_papers gp JOIN subjects s ON gp.subject_id = s.id WHERE gp.faculty_id = :faculty_id ORDER BY gp.generated_at DESC';
$stmt = $pdo->prepare($query);
$stmt->execute([':faculty_id' => $user['id']]);
$papers = $stmt->fetchAll();

render_header('Generated Papers');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Generated Papers</h5>
            <p class="text-muted mb-0">View and download your generated question papers.</p>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Generated At</th>
                    <th>Subject</th>
                    <th>Set</th>
                    <th>Status</th>
                    <th>Downloads</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($papers)): ?>
                    <tr><td colspan="6" class="text-center py-4">No generated papers found.</td></tr>
                <?php endif; ?>
                <?php foreach ($papers as $paper): ?>
                    <tr>
                        <td><?= htmlspecialchars($paper['generated_at']) ?></td>
                        <td><?= htmlspecialchars($paper['subject_name']) ?></td>
                        <td><?= htmlspecialchars($paper['set_type']) ?></td>
                        <td><span class="badge bg-<?= $paper['status'] === 'final' ? 'success' : 'secondary' ?>"><?= htmlspecialchars(ucfirst($paper['status'])) ?></span></td>
                        <td><?= htmlspecialchars($paper['download_count']) ?></td>
                        <td>
                            <a href="<?= htmlspecialchars(base_url('faculty/download_paper.php?id=' . $paper['id'])) ?>" class="btn btn-sm btn-primary" target="_blank">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer();
