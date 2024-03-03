#!/usr/local/bin/php
<?php

use function App\db_connect;
use function App\lock_process;
use function App\truncate_tables;

require __DIR__ . '/../vendor/autoload.php';

lock_process();

$db = db_connect();

truncate_tables($db, ["users", "email_sending_queue", "email_verifying_queue"]);
