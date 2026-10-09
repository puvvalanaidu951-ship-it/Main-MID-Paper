<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    */

    'db' => [

        'host' => 'localhost',

        'name' => 'midexamportal',

        'user' => 'root',

        'pass' => '',

        'charset' => 'utf8mb4',

    ],


    /*
    |--------------------------------------------------------------------------
    | Website
    |--------------------------------------------------------------------------
    */

    'site' => [

        'name' =>
            'College Question Paper Generation & Examination Management System',

        'timezone' =>
            'Asia/Kolkata',

        /*
        | IMPORTANT:
        | This must match your XAMPP project folder.
        */

        'base_url' =>
            'http://localhost/midexamportal',

    ],


    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */

    'email' => [

        'from' =>
            'csmexamcell@adityatekkali.edu.in',

        'from_name' =>
            'Exam Automation System',

        'smtp_host' =>
            'smtp.gmail.com',

        'smtp_user' =>
            'csmexamcell@adityatekkali.edu.in',

        /*
        | Keep your SMTP password here locally.
        | Do not share it publicly.
        */

        'smtp_pass' =>
            'YOUR_SMTP_PASSWORD_HERE',

        'smtp_port' =>
            587,

        'smtp_secure' =>
            'tls',

        'notify_to' => [

            'kvcs@adityatekkali.edu.in',

            'sekhar.chandra178@gmail.com',

            'laxmi.kattamuri@gmail.com',

            'aitamweb@adityatekkali.edu.in'

        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    'security' => [

        /*
        | Session timeout = 30 minutes
        */

        'session_timeout' =>
            1800,

        'csrf_token_name' =>
            'csrf_token',

    ],

];