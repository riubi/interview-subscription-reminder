<?php

require __DIR__ . '/../vendor/autoload.php';

$db = db_connect('database');

$current_time = time();

$one_day_start = strtotime('tomorrow', $current_time);
$one_day_end = strtotime('tomorrow +1 day', $current_time) - 1;
$tree_day_start = strtotime('+3 days', $current_time);
$tree_day_end = strtotime('+4 days', $current_time) - 1;

$sql = "SELECT * FROM users 
        WHERE (
            (validts >= $one_day_start AND validts <= $one_day_end) 
            OR 
            (validts >= $tree_day_start AND validts <= $tree_day_end)
        ) AND (valid = 1 OR confirmed = 1)";

$result = $db->query($sql);

while ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    $email = $row['email'];
    $message = "$username, your subscription is expiring soon.";

    $stmt = $db->prepare("INSERT INTO email_sending_queue (email_to, email_body) VALUES (?, ?)");
    $stmt->bind_param('ss', $email, $message);
    if (!$stmt->execute()) {
        log_error("Error adding to sending queue: " . $stmt->error);
    }
}
