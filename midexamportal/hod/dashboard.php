<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['hod']);
$pdo = Database::connect();
$pending = $pdo->query('SELECT COUNT(*) FROM question_bank WHERE status = "pending"')->fetchColumn();
$approved = $pdo->query('SELECT COUNT(*) FROM question_bank WHERE status = "approved"')->fetchColumn();
render_header('HOD Dashboard');
?>
<div class="row gy-4">
    <div class="col-12">
        <h1 class="h3 mb-3">HOD Dashboard</h1>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Pending Reviews</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($pending) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Approved Questions</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($approved) ?></p>
            </div>
        </div>
    </div>
</div>
<?php render_footer();
