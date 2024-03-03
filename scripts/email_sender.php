<?php

require __DIR__ . '/../vendor/autoload.php';

lock_process();

$db = db_connect('database');

$data_provider = function () use ($db) {
    while (True) {
        $result = $db->query("SELECT * FROM email_sending_queue");

        while ($row = $result->fetch_assoc()) {
            $db->query("DELETE FROM email_sending_queue WHERE id = {$row['id']}");

            yield $row;
        }

        sleep(60);
    }
};

$mailer = function ($data) {
    $is_email_sent = send_email('noreply@example.com', $data['email_to'], $data['email_body']);

    if ($is_email_sent) {
        log_info('Email was sent successfully.', $data);
    } else {
        log_error('Email was not sent.', $data);
    }

    return $is_email_sent;
};

process_queue($mailer, $data_provider);
