<?php

namespace App;

use Exception;

/**
 * Queues user's emails for verification.
 *
 * @param resource $db Database connection resource.
 * @return void
 */
function queues_not_verified_emails($db): void
{
    $current_time = time();

    // Calculate the selection criteria based on the current time.
    $insert_sql = "INSERT INTO email_verifying_queue (email)
                   SELECT email FROM users
                   WHERE validts > {$current_time} AND checked = false AND confirmed != true
                   ON CONFLICT (email) DO NOTHING";

    try {
        db_query($db, $insert_sql);
        log_info("Emails have been enqueued for verification.");
    } catch (Exception $e) {
        log_error("Error enqueuing emails for verification: {$e->getMessage()} .");
    }
}

/**
 * Queues user's emails with soon expired subscription.
 *
 * @param resource $db Database connection resource.
 * @param array $intervals Time intervals.
 * @return void
 */
function queues_subscription_notification($db, array $intervals): void
{
    $sql_intervals = [];
    foreach ($intervals as $interval) {
        $sql_intervals[] = "(validts >= {$interval['start']} AND validts <= {$interval['end']})";
    }

    $sql_filter = implode(" OR ", $sql_intervals);
    // It selects users whose 'validts' falls within the defined intervals and who are either valid or confirmed,
    // and then constructs a reminder message using their username.
    $insert_sql = "INSERT INTO email_sending_queue (email_to, email_body)
                   SELECT email, username || ', your subscription is expiring soon.' AS email_body
                   FROM users
                   WHERE ({$sql_filter}) AND (valid = true OR confirmed = true)";

    try {
        db_query($db, $insert_sql);
        log_info("Subscription reminder emails have been enqueued successfully.");
    } catch (Exception $e) {
        log_error("Error enqueuing subscription reminder emails: {$e->getMessage()}.");
    }
}

/**
 * Inserts a batch of generated users into the DB.
 *
 * @param resource $db Database connection resource.
 * @param int $user_amount Number of users to insert.
 */
function generate_users($db, int $user_amount): void
{
    $stmt_name = 'insert_user';
    $query = "INSERT INTO users (username, email, validts, confirmed)
              VALUES ($1, $2, $3, $4)
              ON CONFLICT (email) DO NOTHING";
    pg_prepare($db, $stmt_name, $query);

    $last_id = get_latest_user_id($db);
    $total = $last_id + $user_amount;

    for ($i = $last_id; $i < $total; $i++) {
        $username = "user{$i}";
        $email = "{$username}@example.com";
        $validts = rand_user_validation();
        $confirmed = rand_user_confirmation();

        pg_execute($db, $stmt_name, [$username, $email, $validts, $confirmed]);

        $current = $i - $last_id + 1;
        if ($current % 50000 === 0) {
            $percents = (int)(100 * $current / $user_amount);
            log_info("Progress: {$current}/{$user_amount}, $percents%.");
        }
    }

    log_info("Done! Inserted {$user_amount} rows in total.");
}

/**
 * Retrieves the next available user ID.
 *
 * @param resource $db Database connection resource.
 * @return int Next available user ID.
 */
function get_latest_user_id($db): int
{
    $result = db_fetch($db, "SELECT COALESCE(MAX(id), 0) AS last_id FROM users");

    return $result
        ? $result[0]['last_id'] + 1
        : 0;
}


/**
 * Generates a validation timestamp with a 20% chance, otherwise returns 0.
 *
 * @return int A future timestamp or 0.
 */
function rand_user_validation(float $chance = 0.2): int
{
    return (mt_rand() / mt_getrandmax() < $chance)
        ? time() + (mt_rand(1, 10) * 86400)
        : 0;
}

/**
 * Randomly returns a confirmation status as a string.
 *
 * @return string 'true' with % probability, 'false' otherwise.
 */
function rand_user_confirmation(float $chance = 0.15): string
{
    return (mt_rand() / mt_getrandmax() < $chance)
        ? 'true'
        : 'false';
}
