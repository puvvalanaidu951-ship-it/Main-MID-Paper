<?php

require_once __DIR__ . '/../includes/Auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/layout.php';

Auth::requireRole(['faculty']);

$user = current_user();

$pdo = Database::connect();


// Total questions
$questions = $pdo->prepare(
    'SELECT COUNT(*)
     FROM question_bank
     WHERE faculty_id = :faculty_id'
);

$questions->execute([
    'faculty_id' => $user['id']
]);

$questionsCount = $questions->fetchColumn();


// Approved questions
$approved = $pdo->prepare(
    'SELECT COUNT(*)
     FROM question_bank
     WHERE faculty_id = :faculty_id
     AND status = :status'
);

$approved->execute([
    'faculty_id' => $user['id'],
    'status' => 'approved'
]);

$approvedCount = $approved->fetchColumn();


// Pending questions
$pending = $pdo->prepare(
    'SELECT COUNT(*)
     FROM question_bank
     WHERE faculty_id = :faculty_id
     AND status = :status'
);

$pending->execute([
    'faculty_id' => $user['id'],
    'status' => 'pending'
]);

$pendingCount = $pending->fetchColumn();


render_header('Faculty Dashboard');

?>

<div class="dashboard-page">

    <!-- Dashboard Heading -->

    <div class="dashboard-heading">

        <div>

            <span class="dashboard-label">
                FACULTY PORTAL
            </span>

            <h1>
                Faculty Dashboard
            </h1>

            <p>
                Manage your question bank, generate examination
                papers and track question approvals.
            </p>

        </div>


        <div class="welcome-badge">

            <span class="welcome-icon">
                🎓
            </span>

            <div>

                <small>
                    Welcome back
                </small>

                <strong>
                    <?= htmlspecialchars($user['name'] ?? 'Faculty') ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- Statistics -->

    <div class="row g-4 dashboard-stats">


        <!-- Total Questions -->

        <div class="col-md-4">

            <div class="stat-card stat-blue">

                <div class="stat-icon">
                    📚
                </div>

                <div class="stat-content">

                    <span>
                        Your Questions
                    </span>

                    <strong>
                        <?= htmlspecialchars($questionsCount) ?>
                    </strong>

                    <small>
                        Total questions added by you
                    </small>

                </div>

                <div class="stat-glow"></div>

            </div>

        </div>


        <!-- Approved -->

        <div class="col-md-4">

            <div class="stat-card stat-green">

                <div class="stat-icon">
                    ✓
                </div>

                <div class="stat-content">

                    <span>
                        Approved
                    </span>

                    <strong>
                        <?= htmlspecialchars($approvedCount) ?>
                    </strong>

                    <small>
                        Questions approved by HOD
                    </small>

                </div>

                <div class="stat-glow"></div>

            </div>

        </div>


        <!-- Pending -->

        <div class="col-md-4">

            <div class="stat-card stat-orange">

                <div class="stat-icon">
                    ⏳
                </div>

                <div class="stat-content">

                    <span>
                        Pending Review
                    </span>

                    <strong>
                        <?= htmlspecialchars($pendingCount) ?>
                    </strong>

                    <small>
                        Questions waiting for review
                    </small>

                </div>

                <div class="stat-glow"></div>

            </div>

        </div>

    </div>


    <!-- Quick Actions -->

    <div class="section-title">

        <span>
            FACULTY TOOLS
        </span>

        <h2>
            Quick Actions
        </h2>

    </div>


    <div class="row g-4">


        <!-- Question Bank -->

        <div class="col-md-6 col-xl-3">

            <a
                href="<?= htmlspecialchars(base_url('/faculty/question_bank.php')) ?>"
                class="action-card"
            >

                <div class="action-icon action-blue">
                    📖
                </div>

                <h3>
                    Question Bank
                </h3>

                <p>
                    Add, manage and review your examination questions.
                </p>

                <span class="action-link">
                    Open Question Bank →
                </span>

            </a>

        </div>


        <!-- Generate Paper -->

        <div class="col-md-6 col-xl-3">

            <a
                href="<?= htmlspecialchars(base_url('/faculty/generate_paper.php')) ?>"
                class="action-card"
            >

                <div class="action-icon action-purple">
                    📝
                </div>

                <h3>
                    Generate Paper
                </h3>

                <p>
                    Create examination papers using approved questions.
                </p>

                <span class="action-link">
                    Generate Paper →
                </span>

            </a>

        </div>


        <!-- Generated Papers -->

        <div class="col-md-6 col-xl-3">

            <a
                href="<?= htmlspecialchars(base_url('/faculty/generated_papers.php')) ?>"
                class="action-card"
            >

                <div class="action-icon action-cyan">
                    📄
                </div>

                <h3>
                    Generated Papers
                </h3>

                <p>
                    View generated examination papers and download them.
                </p>

                <span class="action-link">
                    View Papers →
                </span>

            </a>

        </div>


        <!-- Notifications -->

        <div class="col-md-6 col-xl-3">

            <a
                href="<?= htmlspecialchars(base_url('/faculty/notifications.php')) ?>"
                class="action-card"
            >

                <div class="action-icon action-orange">
                    🔔
                </div>

                <h3>
                    Notifications
                </h3>

                <p>
                    Check approval updates and examination notifications.
                </p>

                <span class="action-link">
                    View Notifications →
                </span>

            </a>

        </div>

    </div>


    <!-- Information Panel -->

    <div class="dashboard-info">

        <div class="info-icon">
            💡
        </div>

        <div>

            <h3>
                Examination Management Portal
            </h3>

            <p>
                Use the Question Bank to manage questions,
                generate examination papers and monitor their
                approval status.
            </p>

        </div>

    </div>

</div>


<?php render_footer(); ?>