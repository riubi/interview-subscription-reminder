#!/usr/local/bin/php
<?php

use function App\db_connect;
use function App\lock_process;
use function App\queues_not_verified_emails;

require __DIR__ . '/../vendor/autoload.php';

lock_process();

$db = db_connect();

queues_not_verified_emails($db);
