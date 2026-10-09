<?php

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/helpers.php';

function render_header(string $title = 'Dashboard'): void
{
    $user = current_user();
    $baseUrl = base_url();

    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= htmlspecialchars($title) ?> | College Exam System
    </title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="<?= htmlspecialchars($baseUrl) ?>/assets/css/style.css"
    >

</head>

<body>

<!-- =====================================================
     TOP NAVBAR
===================================================== -->

<nav class="top-navbar">

    <!-- BRAND -->

    <a
        class="brand"
        href="<?= htmlspecialchars($baseUrl) ?>/index.php"
    >

        <span class="brand-icon">
            <img
                src="<?= htmlspecialchars($baseUrl) ?>/assets/images/aitamLogo.jpg"
                alt="College Logo"
            >
        </span>
        
        <div class="brand-text">

            <strong>
                Exam Portal
            </strong>

            <small>
                College Examination System
            </small>

        </div>

    </a>


    <!-- USER AREA -->

    <div class="top-user">

        <div class="user-avatar">

            <?= htmlspecialchars(
                strtoupper(
                    substr($user['name'] ?? 'U', 0, 1)
                )
            ) ?>

        </div>


        <div class="user-details">

            <small>
                Welcome
            </small>

            <strong>
                <?= htmlspecialchars($user['name'] ?? 'Guest') ?>
            </strong>

        </div>


        <a
            href="<?= htmlspecialchars($baseUrl) ?>/logout.php"
            class="logout-button"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     PORTAL LAYOUT
===================================================== -->

<div class="portal-layout">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">


        <!-- SIDEBAR HEADER -->

        <div class="sidebar-header">

            <span class="menu-label">
                MAIN MENU
            </span>

            <h5>

                <?php if ($user && $user['role_slug'] === 'faculty'): ?>

                    Faculty Portal

                <?php elseif ($user && $user['role_slug'] === 'admin'): ?>

                    Administration

                <?php elseif ($user && $user['role_slug'] === 'hod'): ?>

                    HOD Portal

                <?php else: ?>

                    Portal Menu

                <?php endif; ?>

            </h5>

        </div>


        <!-- =================================================
             SIDEBAR MENU
        ================================================== -->

        <ul class="sidebar-menu">


            <!-- ===============================
                 ADMIN
            ================================ -->

            <?php if ($user && $user['role_slug'] === 'admin'): ?>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/admin/dashboard.php"
                        class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📊
                        </span>

                        <span>
                            Admin Dashboard
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/admin/departments.php"
                        class="<?= $currentPage === 'departments.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            🏢
                        </span>

                        <span>
                            Departments
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/admin/faculty.php"
                        class="<?= $currentPage === 'faculty.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            👨‍🏫
                        </span>

                        <span>
                            Faculty
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/admin/subjects.php"
                        class="<?= $currentPage === 'subjects.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📚
                        </span>

                        <span>
                            Subjects
                        </span>

                    </a>

                </li>


            <!-- ===============================
                 HOD
            ================================ -->

            <?php elseif ($user && $user['role_slug'] === 'hod'): ?>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/hod/dashboard.php"
                        class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📊
                        </span>

                        <span>
                            HOD Dashboard
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/hod/review_questions.php"
                        class="<?= $currentPage === 'review_questions.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            ✓
                        </span>

                        <span>
                            Review Questions
                        </span>

                    </a>

                </li>


            <!-- ===============================
                 FACULTY
            ================================ -->

            <?php elseif ($user && $user['role_slug'] === 'faculty'): ?>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/faculty/dashboard.php"
                        class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📊
                        </span>

                        <span>
                            Faculty Dashboard
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/faculty/question_bank.php"
                        class="<?= $currentPage === 'question_bank.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📚
                        </span>

                        <span>
                            Question Bank
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/faculty/generate_paper.php"
                        class="<?= $currentPage === 'generate_paper.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📝
                        </span>

                        <span>
                            Generate Paper
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/faculty/generated_papers.php"
                        class="<?= $currentPage === 'generated_papers.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            📄
                        </span>

                        <span>
                            Generated Papers
                        </span>

                    </a>

                </li>


                <li>

                    <a
                        href="<?= htmlspecialchars($baseUrl) ?>/faculty/notifications.php"
                        class="<?= $currentPage === 'notifications.php' ? 'active' : '' ?>"
                    >

                        <span class="menu-icon">
                            🔔
                        </span>

                        <span>
                            Notifications
                        </span>

                    </a>

                </li>


            <?php endif; ?>

        </ul>


        <!-- =================================================
             SIDEBAR FOOTER
        ================================================== -->

        <div class="sidebar-footer">

            <div class="security-icon">
                🔐
            </div>

            <div class="security-text">

                <strong>
                    Secure Portal
                </strong>

                <small>
                    Your session is protected
                </small>

            </div>

        </div>

    </aside>


    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="main-content">


        <?php

        $flash = flash_message();

        if ($flash):

        ?>

            <div
                class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show portal-alert"
                role="alert"
            >

                <?= htmlspecialchars($flash['message']) ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"
                ></button>

            </div>

        <?php endif; ?>

<?php
}


/* =========================================================
   FOOTER
========================================================= */

function render_footer(): void
{
    $baseUrl = base_url();
?>

    </main>

</div>


<!-- =====================================================
     BOOTSTRAP JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =====================================================
     CUSTOM JS
===================================================== -->

<script
    src="<?= htmlspecialchars($baseUrl) ?>/assets/js/main.js"
></script>


</body>

</html>

<?php
}
?>