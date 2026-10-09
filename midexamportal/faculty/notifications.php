<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Notification.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['faculty']);
$user = current_user();
$pdo = Database::connect();

if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    Notification::markAsRead($pdo, (int)$_GET['read'], $user['id']);
    redirect(base_url('/faculty/notifications.php'));
}

$notifications = Notification::fetchAll($pdo, $user['id']);

render_header('Notifications');
?>
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="mb-0">Notifications</h5>
        <p class="text-muted mb-0">Latest system messages and workflow updates.</p>
    </div>
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">No notifications at this time.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $note): ?>
                    <div class="list-group-item <?= $note['is_read'] ? 'text-muted' : 'bg-light' ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="mb-1"><?= htmlspecialchars($note['message']) ?></p>
                                <small class="text-muted"><?= htmlspecialchars($note['created_at']) ?></small>
                            </div>
                            <?php if (!$note['is_read']): ?>
                                <a href="<?= htmlspecialchars(base_url('/faculty/notifications.php') . '?read=' . (int)$note['id']) ?>" class="btn btn-sm btn-outline-primary">Mark read</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php render_footer();

