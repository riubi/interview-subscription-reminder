#!/usr/local/bin/php
<?php

use function App\lock_process;
use function App\log_error;
use function App\run_workers;

require __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1], $argv[2]) || !is_numeric($argv[2])) {
    log_error("Usage: php " . basename(__FILE__) . " <script-name> <workers-amount>");
    exit(1);
}

lock_process('workers-' . $argv[1]);

run_workers($argv[1], $argv[2]);
