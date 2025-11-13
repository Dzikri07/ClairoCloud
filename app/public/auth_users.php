<?php
return [
    'admin' => [
        // development default (change segera di production)
        'password' => password_hash('adminpass', PASSWORD_DEFAULT),
        'role' => 'admin',
        'email' => 'admin@example.com'
    ],
    'user' => [
        'password' => password_hash('userpass', PASSWORD_DEFAULT),
        'role' => 'user',
        'email' => 'user@example.com'
    ],
];