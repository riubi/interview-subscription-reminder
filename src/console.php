<?php

function process_queue($handler, $data_provider, $retry_attempts = 0)
{
    $process_name = get_process_name();

    log_info("Process $process_name started.");

    foreach ($data_provider as $data) {
        try {
            while (false == $handler($data) && $retry_attempts-- < 1) {
                log_error("Iteration in $process_name failed", $data);
            }
        } catch (Throwable $throwable) {
            log_error("Iteration in $process_name failed", [
                'exception' => $throwable,
                'data' => $data
            ]);
        }
    }

    log_info("Process $process_name finished.");
}

function lock_process()
{
    $process_name = get_process_name();
    $lock_file = fopen("/tmp/$process_name.lock", 'w+');

    if (!flock($lock_file, LOCK_EX | LOCK_NB)) {
        echo "Script is already running.";
        exit();
    }

    register_shutdown_function(function () use ($lock_file) {
        flock($lock_file, LOCK_UN);
        fclose($lock_file);
    });
}

function get_process_name()
{
    return pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_FILENAME);
}
