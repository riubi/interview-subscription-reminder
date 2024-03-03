<?php

namespace App;

/**
 * Example function to send an email.
 *
 * @param string $from Sender's email address.
 * @param string $to Recipient's email address.
 * @param string $text Email content.
 * @return bool Returns true on success, false on failure.
 */
function send_email(string $from, string $to, string $text): bool
{
    // Something is happening here.
    // Simulate worst case
    sleep(10);

    return true;
}

/**
 * Example function to check an email address.
 *
 * @param string $email
 * @return bool
 */
function check_email(string $email): bool
{
    // Something is happening here.
    sleep(mt_rand(1, 60));

    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
