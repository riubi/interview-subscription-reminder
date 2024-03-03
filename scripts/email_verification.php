<?php

require __DIR__ . '/../vendor/autoload.php';

lock_process();

$db = db_connect('database');

$data_provider = function () use ($db) {
    while (True) {
        $current_time = time();
        $sql = "SELECT * FROM users
                WHERE validts > $current_time AND checked = 0 AND confirmed != 1)";

        $result = $db->query($sql);
        while ($row = $result->fetch_assoc()) {
            yield $row;
        }

        sleep(60);
    }
};

$verifier = function ($data) use ($db) {
    $email = $data['email'];
    $valid_flag = (int)check_email($email);
    if (!$valid_flag) {
        log_warn('Email is not valid.', [
            'email' => $data['email_to']
        ]);
    }

    $sql = "UPDATE users SET valid = ?, checked = 1
            WHERE email = ?";

    $stmt = $db->prepare($sql);
    $stmt->bind_param('is', $valid_flag, $email);
    if (!$stmt->execute()) {
        log_error("Error updating email checks: " . $stmt->error);
    }

    return (bool)$valid_flag;
};

process_queue($verifier, $data_provider);
