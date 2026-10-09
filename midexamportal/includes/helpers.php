<?php

function sanitize_text(string $value): string
{
    return trim(
        htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        )
    );
}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

function redirect(string $url): void
{
    /*
    | If a full URL is supplied, use it directly.
    | Otherwise, automatically add the project base URL.
    */

    if (!filter_var($url, FILTER_VALIDATE_URL)) {

        $url = ltrim($url, '/');

        $url = $url === ''
            ? base_url()
            : base_url($url);
    }

    header('Location: ' . $url);
    exit;
}


/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/

function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {

        $_SESSION['csrf_token'] = bin2hex(
            random_bytes(32)
        );
    }

    return $_SESSION['csrf_token'];
}


/*
|--------------------------------------------------------------------------
| CSRF Check
|--------------------------------------------------------------------------
*/

function csrf_check(string $token): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return hash_equals(
        $_SESSION['csrf_token'] ?? '',
        $token
    );
}


/*
|--------------------------------------------------------------------------
| Base URL
|--------------------------------------------------------------------------
*/

function base_url(string $path = ''): string
{
    $config = require __DIR__ . '/../config/config.php';

    $url = rtrim(
        $config['site']['base_url'],
        '/'
    );

    if ($path === '') {
        return $url;
    }

    return $url . '/' . ltrim($path, '/');
}


/*
|--------------------------------------------------------------------------
| Flash Message
|--------------------------------------------------------------------------
*/

function set_flash(
    string $type,
    string $message
): void {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}


/*
|--------------------------------------------------------------------------
| Get Flash Message
|--------------------------------------------------------------------------
*/

function flash_message(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    return $flash;
}


/*
|--------------------------------------------------------------------------
| Check POST Request
|--------------------------------------------------------------------------
*/

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

function current_user(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user'] ?? null;
}