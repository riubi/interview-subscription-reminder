#!/usr/local/bin/php
<?php

use function App\db_connect;
use function App\lock_process;
use function App\queues_subscription_notification;

require __DIR__ . '/../vendor/autoload.php';

lock_process();

$db = db_connect();

$current_time = time();
// Get time boundaries for the one-day and three-day intervals.
$intervals = [
    [
        "start" => strtotime('tomorrow', $current_time),
        "end" => strtotime('tomorrow +1 day', $current_time) - 1,
    ],
    [
        "start" => strtotime('+3 days', $current_time),
        "end" => strtotime('+4 days', $current_time) - 1,
    ]
];

queues_subscription_notification($db, $intervals);
