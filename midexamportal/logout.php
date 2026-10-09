<?php
require_once __DIR__ . '/includes/Auth.php';
require_once __DIR__ . '/includes/helpers.php';
Auth::logout();
redirect(base_url('/login.php'));

