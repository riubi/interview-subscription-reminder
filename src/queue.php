<?php

namespace App;

use Exception;
use Generator;
use Throwable;

/**
 * Processes the specified queue using a given handler.
 *
 * @param resource $db Database connection resource.
 * @param string $queue_table Name of the queue table.
 * @param callable $handler Callback function to handle a record.
 * @param int $retry_attempts Number of retry attempts per record.
 * @return void
 * @throws Throwable If any error occurs during queue processing.
 */
function handle_queue($db, string $queue_table, callable $handler, int $retry_attempts = 2): void
{
    log_info("Queue '{$queue_table}' started.");

    $queue_records = get_queue_records($db, $queue_table);

    foreach ($queue_records as $record) {
        try {
            $result = handle_queue_record($db, $record, $handler, $retry_attempts);
            if ($result) {
                db_delete($db, $queue_table, ['id' => $record['id']]);
            }
        } catch (Throwable $exception) {
            log_error("Iteration threw an exception.", [
                'exception' => $exception->getMessage(),
                'record' => $record
            ]);
        }
    }

    log_info("Queue '{$queue_table}' finished.");
}

/**
 * Processes a single queue record with retry logic.
 *
 * @param resource $db Database connection resource.
 * @param array $record The queue record.
 * @param callable $handler Callback function to handle the record.
 * @param int $retry_attempts Maximum number of retry attempts.
 * @return mixed  The result from the handler.
 * @throws Exception If the record cannot be processed after the given attempts.
 */
function handle_queue_record($db, array $record, callable $handler, int $retry_attempts)
{
    $attempt = 0;

    while (!($result = $handler($record, $db))) {
        if ($attempt >= $retry_attempts) {
            log_error("Iteration failed after {$retry_attempts} attempts.", [
                'record' => $record
            ]);
            throw new Exception("Record handling failed after {$retry_attempts} attempts.");
        }

        $attempt++;
        log_warn("Queue iteration failed. Attempt {$attempt} of {$retry_attempts}.", [
            'record' => $record
        ]);
        sleep(2);
    }

    return $result;
}

/**
 * Retrieves queue records in batches and marks them as processing.
 *
 * @param resource $db Database connection resource.
 * @param string $queue_table Name of the queue table.
 * @param int $batch_size Number of records per batch.
 * @return Generator Yields each queue record.
 * @throws Throwable If any error occurs during fetching queue records.
 */
function get_queue_records($db, string $queue_table, int $batch_size = 25): Generator
{
    while (true) {
        // Use a transaction to guarantee that the selection and update happen atomically.
        // This is critical to avoid conflicts when the same queue is processed concurrently.
        $batch = with_transaction($db, function ($db) use ($queue_table, $batch_size) {
            $select_query = "SELECT * FROM {$queue_table} WHERE processing IS NULL FOR UPDATE SKIP LOCKED LIMIT {$batch_size}";
            if ($batch_records = db_fetch($db, $select_query)) {
                // Update the 'processing' field for all records in the batch.
                foreach ($batch_records as $record) {
                    db_update($db, $queue_table, ['processing' => time()], ["id" => $record["id"]]);
                }
                log_info("Fetch batch records for processing.", ['count' => count($batch_records)]);
            }

            return $batch_records;
        });

        if (empty($batch)) {
            return;
        }

        foreach ($batch as $record) {
            yield $record;
        }
    }
}
