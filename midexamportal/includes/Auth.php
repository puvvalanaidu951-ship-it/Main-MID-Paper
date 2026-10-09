<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';


class Auth
{

    /*
    |--------------------------------------------------------------------------
    | Start Session
    |--------------------------------------------------------------------------
    */

    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {

            session_start();

        }
    }


    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public static function login(
        string $email,
        string $password
    ): ?array {

        $pdo = Database::connect();


        $stmt = $pdo->prepare(
            'SELECT
                u.id,
                u.name,
                u.email,
                u.password,
                u.department_id,
                r.slug AS role_slug,
                r.name AS role_name
             FROM users u
             JOIN roles r
                ON u.role_id = r.id
             WHERE u.email = :email
               AND u.status = 1
             LIMIT 1'
        );


        $stmt->execute([
            ':email' => $email
        ]);


        $user = $stmt->fetch();


        if (
            $user &&
            password_verify(
                $password,
                $user['password']
            )
        ) {

            self::startSession();


            /*
            | Regenerate session ID after login
            | for better session security.
            */

            session_regenerate_id(true);


            $_SESSION['user'] = [

                'id' =>
                    (int)$user['id'],

                'name' =>
                    $user['name'],

                'email' =>
                    $user['email'],

                'department_id' =>
                    $user['department_id']
                        ? (int)$user['department_id']
                        : null,

                'role_slug' =>
                    $user['role_slug'],

                'role_name' =>
                    $user['role_name'],

                'last_activity' =>
                    time(),

            ];


            return $_SESSION['user'];
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Check Login
    |--------------------------------------------------------------------------
    */

    public static function check(): bool
    {

        self::startSession();


        if (empty($_SESSION['user'])) {

            return false;

        }


        $config =
            require __DIR__ . '/../config/config.php';


        $lastActivity =
            $_SESSION['user']['last_activity']
            ?? 0;


        /*
        | Session timeout
        */

        if (
            time() - $lastActivity >
            $config['security']['session_timeout']
        ) {

            self::logout();

            return false;
        }


        /*
        | Update activity time
        */

        $_SESSION['user']['last_activity'] =
            time();


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public static function logout(): void
    {

        self::startSession();


        $_SESSION = [];


        if (ini_get('session.use_cookies')) {

            $params =
                session_get_cookie_params();


            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }


        session_destroy();
    }


    /*
    |--------------------------------------------------------------------------
    | Require Role
    |--------------------------------------------------------------------------
    */

    public static function requireRole(
        array $roles
    ): void {

        if (
            !self::check() ||
            !in_array(
                $_SESSION['user']['role_slug'],
                $roles,
                true
            )
        ) {

            redirect(base_url('/index.php'));
        }
    }

}