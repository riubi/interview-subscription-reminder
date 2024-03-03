<?php

namespace App;

use Exception;
use Throwable;

/**
 * Establishes a connection to the PostgreSQL database using pg_connect.
 *
 * @return resource Connection resource.
 * @throws Exception If the connection fails.
 */
function db_connect()
{
    $host = getenv('DB_HOST') ?: 'localhost';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'mydb';
    $user = getenv('DB_USER') ?: 'myuser';
    $password = getenv('DB_PASSWORD') ?: 'mypass';

    $conn_string = "host={$host} port={$port} dbname={$dbname} user={$user} password={$password}";
    $db = pg_connect($conn_string, PGSQL_CONNECT_FORCE_NEW);

    if (!$db) {
        log_error("Failed to connect to the database with parameters: host={$host}, port={$port}, dbname={$dbname}.");
        throw new Exception("Failed to connect to the database.");
    }

    log_info("Database connection to '{$dbname}' established.");

    // Register a shutdown function to close the connection when the script terminates.
    register_shutdown_function(function () use ($db, $dbname) {
        pg_close($db);
        log_info("Database connection to '{$dbname}' closed.");
    });

    return $db;
}

/**
 * Executes a function within a database transaction.
 *
 * @param resource $db Connection resource.
 * @param callable $callback The callback to execute within the transaction.
 * @return mixed The result of the callback.
 * @throws Throwable If any error occurs during the transaction.
 */
function with_transaction($db, callable $callback)
{
    db_query($db, "BEGIN");
    try {
        $result = $callback($db);
        db_query($db, "COMMIT");

        return $result;
    } catch (Throwable $exception) {
        log_error("Transaction rolled back due to error: {$exception->getMessage()}.");
        db_query($db, "ROLLBACK");

        throw $exception;
    }
}

/**
 * Executes a database query.
 *
 * @param resource $db Connection resource.
 * @param string $query The SQL query to execute.
 * @return resource The query result resource.
 * @throws Exception If the query fails.
 */
function db_query($db, string $query)
{
    if (!$result = pg_query($db, $query)) {
        throw new Exception("Failed to execute query: {$query}, reason: " . pg_last_error($db));
    }

    return $result;
}

/**
 * Fetches rows from a query result as a generator.
 *
 * @param resource $db Connection resource.
 * @param string $query The SQL query to execute.
 * @return array Rows as associative arrays.
 * @throws Exception If the query fails.
 */
function db_fetch($db, string $query): array
{
    $result = db_query($db, $query);
    $rows = [];
    while ($row = pg_fetch_array($result)) {
        $rows[] = $row;
    }

    return $rows;
}

/**
 * Updates rows in a table using pg_update.
 *
 * @param resource $db Connection resource.
 * @param string $table The table name.
 * @param array $fields An associative array of fields to update.
 * @param array $condition An associative array representing the WHERE condition.
 * @return bool True on success.
 * @throws Exception If the update fails.
 */
function db_update($db, string $table, array $fields = [], array $condition = []): bool
{
    if (!$result = pg_update($db, $table, $fields, $condition)) {
        throw new Exception("Failed to update table '{$table}': " . pg_last_error($db));
    }

    return $result;
}

/**
 * Deletes rows from a table using pg_delete.
 *
 * @param resource $db Connection resource.
 * @param string $table The table name.
 * @param array $condition An associative array representing the WHERE condition.
 * @return bool True on success.
 * @throws Exception If the deletion fails.
 */
function db_delete($db, string $table, array $condition): bool
{
    if (!$result = pg_delete($db, $table, $condition)) {
        throw new Exception("Failed to delete from table '{$table}': " . pg_last_error($db));
    }

    return $result;
}

/**
 * Truncates multiple tables in the database.
 *
 * @param resource $db Connection resource.
 * @param string[] $tables Table names to truncate.
 *
 * @return void
 */
function truncate_tables($db, array $tables)
{
    try {
        foreach ($tables as $table) {
            db_query($db, "TRUNCATE TABLE $table RESTART IDENTITY CASCADE");
        }

        log_info("All specified tables have been truncated successfully.");
    } catch (Exception $e) {
        log_error("Error truncating tables: " . $e->getMessage() . ".");
    }
}
