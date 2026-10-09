<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['coe']);
$pdo = Database::connect();
$generated = $pdo->query('SELECT COUNT(*) FROM generated_papers')->fetchColumn();
$today = $pdo->query('SELECT COUNT(*) FROM generated_papers WHERE DATE(generated_at) = CURDATE()')->fetchColumn();
render_header('COE Dashboard');
?>
<div class="row gy-4">
    <div class="col-12">
        <h1 class="h3 mb-3">Controller of Examinations Dashboard</h1>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Total Generated Papers</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($generated) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Today's Papers</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($today) ?></p>
            </div>
        </div>
    </div>
</div>
<?php render_footer();
