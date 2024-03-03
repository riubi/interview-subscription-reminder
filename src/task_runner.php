<?php

namespace App;

/**
 * Retrieves the name of the current process (script).
 *
 * @return string
 */
function get_process_name(): string
{
    $process_name = pathinfo($_SERVER['SCRIPT_NAME'] ?? 'cli', PATHINFO_FILENAME);

    return getenv('WORKER_MODE') === '1'
        ? $process_name . '~child#' . getmypid()
        : $process_name;
}

/**
 * Runs worker processes and waits for all to finish.
 *
 * @param string $worker_command Command to execute in each worker (e.g., "php /app/scripts/worker.php").
 * @param int $num_workers Number of worker processes to start.
 * @return void
 */
function run_workers(string $worker_command, int $num_workers = 1): void
{
    $processes = [];

    // Set up environment for child processes.
    $env = $_ENV;
    $env['WORKER_MODE'] = '1';

    // Spawn worker processes.
    for ($i = 0; $i < $num_workers; $i++) {
        // Append the worker ID as an argument.
        $command = $worker_command . " " . $i;
        $descriptor_spec = [
            0 => ["pipe", "r"], // STDIN: We'll leave this as a pipe.
            // Redirect STDOUT to Docker container's stdout.
            1 => ["file", "/dev/stdout", "a"],
            // Redirect STDERR to Docker container's stderr.
            2 => ["file", "/dev/stderr", "a"]
        ];

        $process = proc_open($command, $descriptor_spec, $pipes, null, $env);
        if (!is_resource($process)) {
            log_error("Failed to start worker process #{$i}.");
        } else {
            $processes[] = ['process' => $process, 'pipes' => $pipes];
        }
    }

    // Wait for all worker processes to finish.
    foreach ($processes as $proc_info) {
        if (isset($proc_info['pipes'][0])) {
            fclose($proc_info['pipes'][0]);
        }
        $exit_code = proc_close($proc_info['process']);
        if ($exit_code != 0) {
            log_error("Worker finished with error, exit code: {$exit_code}.");
        }
    }

    log_info("Workers finished.");
}

/**
 * Locks the process to prevent multiple instances from running simultaneously.
 *
 * @param string|null $lock_name
 * @return void
 */
function lock_process(?string $lock_name = null): void
{
    $process_name = $lock_name ?: get_process_name();
    $lock_file_path = "/tmp/{$process_name}.lock";
    $lock_file = fopen($lock_file_path, 'c');

    if (!$lock_file) {
        log_error("Unable to create lock file for process {$process_name}.");
        exit(1);
    }

    if (!flock($lock_file, LOCK_EX | LOCK_NB)) {
        log_error("Process {$process_name} is already running.");
        fclose($lock_file);
        exit(1);
    }

    log_info("Lock acquired for process {$process_name}.");

    // Register a shutdown function to release the lock when the process terminates.
    register_shutdown_function(function () use ($lock_file, $process_name) {
        flock($lock_file, LOCK_UN);
        fclose($lock_file);
        log_info("Lock released for process {$process_name}.");
    });
}
