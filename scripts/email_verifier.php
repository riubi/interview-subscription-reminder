#!/usr/local/bin/php
<?php

use function App\check_email;
use function App\db_connect;
use function App\db_update;
use function App\handle_queue;
use function App\log_error;
use function App\log_info;

require __DIR__ . '/../vendor/autoload.php';

$db = db_connect();

handle_queue($db, 'email_verifying_queue', function (array $record, $db) {
    $email = $record['email'];
    $is_valid = check_email($email);

    db_update($db, 'users', ['valid' => $is_valid, 'checked' => 1], ['email' => $email]);

    if ($is_valid) {
        log_info("Email verified successfully.", ['email' => $email]);
    } else {
        log_error("Email verification failed.", ['email' => $email]);
    }

    return $is_valid;
});
