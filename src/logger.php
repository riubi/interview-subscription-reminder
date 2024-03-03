<?php

namespace App;

/**
 * Logs an informational message.
 *
 * @param string $message The message to log.
 * @param array $context Additional context (optional).
 * @return void
 */
function log_info(string $message, array $context = []): void
{
    log_message('INFO', $message, $context);
}

/**
 * Logs a warning message.
 *
 * @param string $message The warning message to log.
 * @param array $context Additional context (optional).
 * @return void
 */
function log_warn(string $message, array $context = []): void
{
    log_message('WARN', $message, $context);
}

/**
 * Logs an error message.
 *
 * @param string $message The error message to log.
 * @param array $context Additional context (optional).
 * @return void
 */
function log_error(string $message, array $context = []): void
{
    log_message('ERROR', $message, $context);
}

/**
 * Default log implementation that handles all log levels.
 *
 * @param string $level Log level (e.g., INFO, WARN, ERROR).
 * @param string $message The message to log.
 * @param array $context Additional context (optional).
 * @return void
 */
function log_message(string $level, string $message, array $context = []): void
{
    $process_name = get_process_name();
    $context_str = !empty($context) ? ' Context: ' . json_encode($context) . "." : '';

    echo "{$process_name}: [{$level}] {$message}{$context_str}\n";
}