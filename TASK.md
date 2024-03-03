# PHP Developer Test Cases

You are developing a service for sending notifications about expiring subscriptions. It is required to send an email to the user one and three days before the subscription expires with the text "{username}, your subscription is expiring soon".

## Specifications

1. **Database Table with Users (5,000,000+ rows):**
    - `username`: Username
    - `email`: Email
    - `validts`: Unix timestamp until which the monthly subscription is valid, or 0 if there is no subscription
    - `confirmed`: 0 or 1, depending on whether the user has confirmed their email via a link (a unique link is sent to the specified email after registration, if they click on the link in the email, this field is set to 1)
    - `checked`: Whether the email has been checked for validation (1) or not (0)
    - `valid`: Whether the email is valid (1) or not (0)

2. **About 80% of users do not have a subscription.**

3. **Only 15% of users confirm their email (the `confirmed` field).**

4. **External Function `check_email($email)`**
    - Checks the email for validity (the email is guaranteed to be valid) and returns 0 or 1. The function takes from 1 second to 1 minute to execute. Each function call costs 1 ruble.

5. **Function `send_email($from, $to, $text)`**
    - Sends an email. The function takes from 1 second to 10 seconds to execute.

## Constraints

1. It is necessary to regularly send emails about the expiration of subscriptions to those emails that are guaranteed to be valid.
2. You can use cron.
3. You can create necessary tables in the DB or modify existing ones.
4. Implement "dummy" functions for `check_email` and `send_email`.
5. Do not use OOP.
6. Implement queues without using queue managers.

## Advantages

1. Simple, readable, and working code.
2. Readme.
3. Code hosted on GitHub.

This test task aims to understand your thought process and ability to find solutions to the tasks presented. There is no need to demonstrate a variety of technologies you are proficient in. The code should be simple and address the task at hand.