<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();

$message = '';

if (is_post()) {

    $email = sanitize_text($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!csrf_check($token)) {

        $message = 'Invalid CSRF token. Please refresh and try again.';

    } elseif (empty($email) || empty($password)) {

        $message = 'Email and password are required.';

    } else {

        $user = Auth::login($email, $password);

        if ($user) {
            redirect(base_url('/index.php'));
        }

        $message = 'Invalid credentials or account inactive.';
    }
}

$csrf = csrf_token();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Login | College Exam System</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Portal CSS -->

    <link
        href="assets/css/style.css"
        rel="stylesheet"
    >


    <style>

        /* =====================================================
           LOGIN PAGE
        ===================================================== */

        * {
            box-sizing: border-box;
        }


        body.login-page {

            margin: 0;

            min-height: 100vh;

            font-family: Arial, Helvetica, sans-serif;

            background:
                radial-gradient(
                    circle at 10% 15%,
                    rgba(59, 130, 246, 0.18),
                    transparent 35%
                ),
                radial-gradient(
                    circle at 90% 85%,
                    rgba(139, 92, 246, 0.18),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #050816 0%,
                    #0b1023 50%,
                    #111936 100%
                );

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

            position: relative;

            overflow-x: hidden;
        }


        /* =====================================================
           BACKGROUND EFFECTS
        ===================================================== */

        .login-page::before {

            content: "";

            position: fixed;

            width: 450px;

            height: 450px;

            border-radius: 50%;

            background:
                rgba(245, 197, 66, 0.05);

            top: -200px;

            left: -200px;

            filter: blur(10px);

            pointer-events: none;
        }


        .login-page::after {

            content: "";

            position: fixed;

            width: 400px;

            height: 400px;

            border-radius: 50%;

            background:
                rgba(6, 182, 212, 0.06);

            right: -180px;

            bottom: -180px;

            filter: blur(10px);

            pointer-events: none;
        }


        /* =====================================================
           MAIN WRAPPER
        ===================================================== */

        .login-wrapper {

            width: 100%;

            max-width: 1050px;

            position: relative;

            z-index: 2;
        }


        /* =====================================================
           LOGIN CARD
        ===================================================== */

        .login-card {

            display: grid;

            grid-template-columns: 1fr 1fr;

            background:
                rgba(15, 23, 48, 0.78);

            border:
                1px solid rgba(255, 255, 255, 0.12);

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 30px 80px rgba(0, 0, 0, 0.50),
                0 0 40px rgba(59, 130, 246, 0.08);

            backdrop-filter: blur(25px);

            -webkit-backdrop-filter: blur(25px);
        }


        /* =====================================================
           LEFT BRANDING PANEL
        ===================================================== */

        .login-brand-panel {

            padding: 55px 45px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            background:
                linear-gradient(
                    145deg,
                    rgba(59, 130, 246, 0.12),
                    rgba(139, 92, 246, 0.10)
                );

            border-right:
                1px solid rgba(255, 255, 255, 0.08);

            position: relative;
        }


        /* Logo */

        .brand-logo {

            width: 95px;

            height: 95px;

            object-fit: contain;

            background: #ffffff;

            padding: 8px;

            border-radius: 18px;

            margin-bottom: 28px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.30),
                0 0 25px rgba(245, 197, 66, 0.12);
        }


        /* Brand tag */

        .brand-tag {

            display: inline-flex;

            align-items: center;

            width: fit-content;

            padding: 7px 13px;

            border-radius: 30px;

            background:
                rgba(245, 197, 66, 0.08);

            border:
                1px solid rgba(245, 197, 66, 0.30);

            color: #FFD700;

            font-size: 12px;

            font-weight: 700;

            letter-spacing: 1.5px;

            margin-bottom: 18px;
        }


        /* Heading */

        .login-brand-panel h1 {

            font-size: 36px;

            font-weight: 800;

            line-height: 1.2;

            margin-bottom: 16px;

            color: #ffffff;
        }


        .login-brand-panel h1 span {

            color: #FFD700;
        }


        .login-brand-panel p {

            color: #cbd5e1;

            font-size: 15px;

            line-height: 1.7;

            max-width: 430px;

            margin-bottom: 28px;
        }


        /* =====================================================
           FEATURES
        ===================================================== */

        .portal-features {

            display: flex;

            flex-direction: column;

            gap: 13px;
        }


        .portal-feature {

            display: flex;

            align-items: center;

            gap: 12px;

            color: #e2e8f0;

            font-size: 14px;
        }


        .feature-icon {

            width: 30px;

            height: 30px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            background:
                rgba(245, 197, 66, 0.08);

            border:
                1px solid rgba(245, 197, 66, 0.20);

            color: #FFD700;

            font-size: 14px;
        }


        /* =====================================================
           RIGHT LOGIN PANEL
        ===================================================== */

        .login-form-panel {

            padding: 55px 45px;

            background:
                rgba(5, 8, 22, 0.45);

            display: flex;

            flex-direction: column;

            justify-content: center;
        }


        /* Login heading */

        .login-header {

            margin-bottom: 30px;
        }


        .login-header h2 {

            font-size: 30px;

            font-weight: 800;

            margin-bottom: 8px;

            color: #ffffff;
        }


        .login-header p {

            margin: 0;

            color: #94a3b8;

            font-size: 14px;
        }


        /* =====================================================
           FORM LABELS
        ===================================================== */

        .login-form .form-label {

            color: #FFD700;

            font-weight: 600;

            font-size: 13px;

            margin-bottom: 8px;
        }


        /* =====================================================
           INPUT WRAPPER
        ===================================================== */

        .input-wrapper {

            position: relative;

            width: 100%;
        }


        /* =====================================================
           LEFT INPUT ICON
        ===================================================== */

        .input-icon {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #94a3b8;

            font-size: 17px;

            pointer-events: none;

            z-index: 3;
        }


       /* =====================================================
   TRANSPARENT GLASS INPUT BOX
===================================================== */

.login-form .form-control {
    width: 100%;
    height: 54px;

    /* Fully transparent */
    background: transparent !important;
    background-color: transparent !important;

    border: 1px solid rgba(255, 255, 255, 0.28) !important;
    border-radius: 13px;

    color: #ffffff !important;

    padding-left: 45px;
    padding-right: 50px;

    font-size: 14px;

    outline: none;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.08),
        0 4px 15px rgba(0, 0, 0, 0.10) !important;

    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);

    transition:
        border-color 0.25s ease,
        background-color 0.25s ease,
        box-shadow 0.25s ease;
}


/* Placeholder */

.login-form .form-control::placeholder {
    color: rgba(255, 255, 255, 0.50) !important;
}


/* Hover */

.login-form .form-control:hover {
    background: rgba(255, 255, 255, 0.035) !important;

    border-color:
        rgba(255, 255, 255, 0.42) !important;

    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.10),
        0 5px 18px rgba(0, 0, 0, 0.15) !important;
}


/* Focus */

.login-form .form-control:focus {
    background:
        rgba(255, 255, 255, 0.045) !important;

    border-color:
        #FFD700 !important;

    color: #ffffff !important;

    box-shadow:
        0 0 0 3px rgba(255, 215, 0, 0.10),
        inset 0 1px 0 rgba(255, 255, 255, 0.10),
        0 6px 20px rgba(0, 0, 0, 0.15) !important;

    outline: none !important;
}


/* Remove Bootstrap autofill background */

.login-form .form-control:-webkit-autofill,
.login-form .form-control:-webkit-autofill:hover,
.login-form .form-control:-webkit-autofill:focus {
    -webkit-text-fill-color: #ffffff !important;

    -webkit-box-shadow:
        0 0 0 1000px transparent inset !important;

    box-shadow:
        0 0 0 1000px transparent inset !important;

    transition:
        background-color 9999s ease-in-out 0s;
}
        /* =====================================================
           PASSWORD VIEW BUTTON
        ===================================================== */

        .password-toggle {

            position: absolute;

            right: 10px;

            top: 50%;

            transform: translateY(-50%);

            width: 38px;

            height: 38px;

            display: flex;

            align-items: center;

            justify-content: center;

            border: none;

            outline: none;

            background:
                transparent;

            color: #94a3b8;

            cursor: pointer;

            font-size: 18px;

            padding: 0;

            border-radius: 8px;

            z-index: 10;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }


        .password-toggle:hover {

            color: #FFD700;

            background:
                rgba(255, 215, 0, 0.10);
        }


        .password-toggle:focus {

            color: #FFD700;

            background:
                rgba(255, 215, 0, 0.10);

            outline: none;

            box-shadow: none;
        }


        .password-toggle:active {

            transform:
                translateY(-50%) scale(0.92);
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .login-button {

            width: 100%;

            height: 54px;

            border: none;

            border-radius: 13px;

            background:
                linear-gradient(
                    135deg,
                    #FFD700,
                    #f5c542
                );

            color: #111111;

            font-size: 15px;

            font-weight: 800;

            letter-spacing: 0.3px;

            cursor: pointer;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;

            box-shadow:
                0 8px 25px rgba(245, 197, 66, 0.18);
        }


        .login-button:hover {

            transform: translateY(-2px);

            background:
                linear-gradient(
                    135deg,
                    #FFE66D,
                    #FFD700
                );

            box-shadow:
                0 12px 30px rgba(245, 197, 66, 0.28);
        }


        .login-button:active {

            transform: translateY(0);
        }


        /* =====================================================
           SECURITY MESSAGE
        ===================================================== */

        .security-message {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            margin-top: 22px;

            color: #94a3b8;

            font-size: 12px;

            text-align: center;
        }


        .security-message .security-icon {

            color: #10b981;

            font-size: 15px;
        }


        /* =====================================================
           ERROR MESSAGE
        ===================================================== */

        .login-alert {

            background:
                rgba(239, 68, 68, 0.10);

            border:
                1px solid rgba(239, 68, 68, 0.30);

            color: #fca5a5;

            border-radius: 10px;

            font-size: 13px;

            padding: 12px 14px;

            margin-bottom: 20px;
        }


        /* =====================================================
           FOOTER
        ===================================================== */

        .login-footer {

            text-align: center;

            margin-top: 22px;

            color: #64748b;

            font-size: 12px;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .login-card {

                grid-template-columns: 1fr;
            }


            .login-brand-panel {

                padding: 40px 35px;

                border-right: none;

                border-bottom:
                    1px solid rgba(255, 255, 255, 0.08);
            }


            .login-form-panel {

                padding: 40px 35px;
            }


            .login-brand-panel h1 {

                font-size: 30px;
            }
        }


        @media (max-width: 500px) {

            body.login-page {

                padding: 15px;
            }


            .login-card {

                border-radius: 18px;
            }


            .login-brand-panel,
            .login-form-panel {

                padding: 30px 22px;
            }


            .login-brand-panel h1 {

                font-size: 27px;
            }


            .login-header h2 {

                font-size: 26px;
            }


            .brand-logo {

                width: 80px;

                height: 80px;
            }
        }

    </style>

</head>


<body class="login-page">


<div class="login-wrapper">

    <div class="login-card">


        <!-- =================================================
             LEFT BRANDING PANEL
        ================================================== -->

        <div class="login-brand-panel">


            <img
                src="assets/images/aitamLogo.jpg"
                alt="AITAM College Logo"
                class="brand-logo"
            >


            <span class="brand-tag">
                COLLEGE EXAMINATION SYSTEM
            </span>


            <h1>

                College Exam

                <span>
                    Automation
                </span>

            </h1>


            <p>

                A secure examination management portal for
                faculty and administration to manage questions,
                generate examination papers and monitor approvals.

            </p>


            <div class="portal-features">


                <div class="portal-feature">

                    <span class="feature-icon">
                        ✓
                    </span>

                    <span>
                        Secure Faculty Access
                    </span>

                </div>


                <div class="portal-feature">

                    <span class="feature-icon">
                        📚
                    </span>

                    <span>
                        Question Bank Management
                    </span>

                </div>


                <div class="portal-feature">

                    <span class="feature-icon">
                        📝
                    </span>

                    <span>
                        Automated Paper Generation
                    </span>

                </div>


                <div class="portal-feature">

                    <span class="feature-icon">
                        🔐
                    </span>

                    <span>
                        Protected Examination Data
                    </span>

                </div>


            </div>

        </div>


        <!-- =================================================
             RIGHT LOGIN PANEL
        ================================================== -->

        <div class="login-form-panel">


            <div class="login-header">

                <h2>
                    Welcome Back
                </h2>

                <p>
                    Sign in to access your examination portal.
                </p>

            </div>


            <?php if ($message): ?>

                <div class="login-alert">

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <form
                method="post"
                class="login-form"
                novalidate
            >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf) ?>"
                >


                <!-- =================================================
                     EMAIL
                ================================================== -->

                <div class="mb-4">


                    <label class="form-label">
                        Email Address
                    </label>


                    <div class="input-wrapper">


                        <span class="input-icon">
                            ✉
                        </span>


                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email address"
                            autocomplete="email"
                            required
                            autofocus
                        >


                    </div>

                </div>


                <!-- =================================================
                     PASSWORD
                ================================================== -->

                <div class="mb-4">


                    <label class="form-label">
                        Password
                    </label>


                    <div class="input-wrapper">


                        <span class="input-icon">
                            🔒
                        </span>


                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control password-input"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >


                        <button
                            type="button"
                            id="passwordToggle"
                            class="password-toggle"
                            aria-label="Show password"
                            title="Show password"
                        >
                            👁
                        </button>


                    </div>

                </div>


                <!-- =================================================
                     LOGIN BUTTON
                ================================================== -->

                <div>

                    <button
                        type="submit"
                        class="login-button"
                    >
                        Sign In to Portal
                    </button>

                </div>


            </form>


            <!-- Security -->

            <div class="security-message">

                <span class="security-icon">
                    🔐
                </span>

                <span>
                    Secure connection • Your credentials are protected
                </span>

            </div>


            <!-- Footer -->

            <div class="login-footer">

                College Examination Management System

            </div>


        </div>

    </div>

</div>


<!-- =====================================================
     BOOTSTRAP JAVASCRIPT
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
></script>


<!-- =====================================================
     PASSWORD SHOW / HIDE
===================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const passwordInput =
        document.getElementById('password');

    const passwordToggle =
        document.getElementById('passwordToggle');


    if (!passwordInput || !passwordToggle) {
        return;
    }


    passwordToggle.addEventListener('click', function () {

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            passwordToggle.textContent = '🙈';

            passwordToggle.setAttribute(
                'aria-label',
                'Hide password'
            );

            passwordToggle.setAttribute(
                'title',
                'Hide password'
            );

        } else {

            passwordInput.type = 'password';

            passwordToggle.textContent = '👁';

            passwordToggle.setAttribute(
                'aria-label',
                'Show password'
            );

            passwordToggle.setAttribute(
                'title',
                'Show password'
            );

        }

    });

});

</script>


</body>

</html>