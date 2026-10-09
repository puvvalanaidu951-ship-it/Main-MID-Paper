<?php
require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['principal']);
$pdo = Database::connect();
$approved = $pdo->query('SELECT COUNT(*) FROM question_bank WHERE status = "approved"')->fetchColumn();
$generated = $pdo->query('SELECT COUNT(*) FROM generated_papers')->fetchColumn();
render_header('Principal Dashboard');
?>
<div class="row gy-4">
    <div class="col-12">
        <h1 class="h3 mb-3">Principal Dashboard</h1>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Approved Questions</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($approved) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>Generated Papers</h5>
                <p class="display-6 mb-0"><?= htmlspecialchars($generated) ?></p>
            </div>
        </div>
    </div>
</div>
<?php render_footer();
