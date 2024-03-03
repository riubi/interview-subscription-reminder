#!/usr/local/bin/php
<?php

use function App\db_connect;
use function App\handle_queue;
use function App\log_error;
use function App\log_info;
use function App\send_email;

require __DIR__ . '/../vendor/autoload.php';

$db = db_connect();

handle_queue($db, 'email_sending_queue', function (array $record) {
    $from = getenv("DEFAULT_EMAIL");
    $to = $record['email_to'];
    $body = $record['email_body'];

    if ($result = send_email($from, $to, $body)) {
        log_info("Email sent successfully.", ['to' => $to]);
    } else {
        log_error("Failed to send email.", ['to' => $to]);
    }

    return $result;
});
