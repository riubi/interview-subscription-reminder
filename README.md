# Interview Email Subscription Reminder

## Description

What's going on here? So... These scripts are designed to send notifications to users about their expiring
subscriptions. It alerts users one and three days before their subscription ends with the message "{username}, your
subscription is expiring soon". Detailed information about the task can be found on [TASK.md](TASK.md).

## Important Notes

- \>5 000 000 * 0.2 * 0.85 >= 850 000 - these are completely approximate numbers of unconfirmed emails with a
  subscription.
- Since email verification is a paid service, it is economical to verify only those emails that are associated with an
  active subscription.
- The `check_email` script can take up to 1 minute to verify an email (60 * 24 = 1 440 per day in worst case), which
  could become a bottleneck.
- Considering the above, checking before sending email is not an option at all. It is better to move the verification
  into a separate job.
- Tables contain basic indexes and are not expanded due to possible low fields selectivity, so they should be expanded
  with a better understanding of the real db cases.
- Function `lock_process` is used to prevent duplicate runs.

## Running the Service

1. Ensure that PHP and Composer are installed on your system.
2. Run `composer install` to install all dependencies.
3. Set up the database using `schema.sql`.
4. Define the necessary cron jobs in `scheduler.cron`.
5. To start the service, execute the scripts located in the `/scripts` directory.
