#!/usr/local/bin/php
<?php

use function App\db_connect;
use function App\generate_users;
use function App\lock_process;
use function App\log_error;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1]) || !is_numeric($argv[1])) {
    log_error("Usage: php " . basename(__FILE__) . " <user_amount>.");
    exit(1);
}

lock_process();

$db = db_connect();

generate_users($db, $argv[1]);
