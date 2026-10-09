<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/helpers.php';

Auth::startSession();
if (Auth::check()) {
    $user = current_user();
    switch ($user['role_slug']) {
        case 'admin':
            redirect(base_url('/admin/dashboard.php'));
        case 'principal':
            redirect(base_url('/principal/dashboard.php'));
        case 'dean':
            redirect(base_url('/dean/dashboard.php'));
        case 'coe':
            redirect(base_url('/coe/dashboard.php'));
        case 'hod':
            redirect(base_url('/hod/dashboard.php'));
        case 'faculty':
            redirect(base_url('/faculty/dashboard.php'));
        default:
            Auth::logout();
            redirect(base_url('/login.php'));
    }
}
redirect(base_url('/login.php'));

