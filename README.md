# Interview Email Subscription Reminder


## What's going on here?
So... This is the implementation of a test task.
Detailed information about the task can be found on [TASK.md](TASK.md).


## Short description
These scripts are designed to send notifications to users about their expiring subscriptions.
It alerts users one and three days before their subscription ends with the message "{username}, 
your subscription is expiring soon".


## Restriction
1. We need to regularly send subscription-expiration emails only to addresses that will definitely receive them.
2. We can use cron.
3. We can create or modify existing DB tables.
4. For check_email and send_email, just write stubs.
5. No OOP.
6. Implement queues without any managers.


## Important Notes

- **Scale & Volume:**  
  System handles *>5,000,000* users, with ~20% having subscriptions and ~85% confirming emails. Target audience for verification/reminders: *≥850,000* users.

- **Bottleneck Risk:**  
  `check_email` takes up to 1 min per email (1,440/day max). Requires ~104 workers for 24-hour verification; parallelization is critical.

- **Parallelization:**  
  Uses `proc_open` for concurrency; `workers` script can isolate queue consumers. Alternatively, each worker process can be isolated in its own Docker container for better scalability and resource management.

- **Error Handling:**  
  Errors are caught and logged using Exceptions/Throwables to ensure process stability.

- **Additional Considerations:**  
  The system is fully containerized using Docker, and tasks are orchestrated via cron jobs. `lock_process` prevents duplicate runs.


## Running the Service
To set up and run the service, follow these steps:
1. Use Docker Compose to bring up the service in detached mode:
   ```bash
   docker compose up -d
   ```
2. Populate the database with test data (e.g., 250,000 records):
   ```bash
   docker compose exec app data_generate 250000
   ```
3. Load unverified email addresses into the verification queue:
   ```bash
   docker compose exec app email_verification_preloader
   ```
4. Start worker processes to verify email addresses (e.g., 25 workers):
   ```bash
   docker compose exec app workers email_verifier 25
   ```
5. Generate and queue subscription expiration reminders:
   ```bash
   docker compose exec app subscription_reminder
   ```
6. Start worker processes to send notification emails (e.g., 25 workers):
   ```bash
   docker compose exec app workers email_sender 25
   ```
7. After testing, truncate all generated data to reset the system:
   ```bash
   docker compose exec app data_truncate
   ```
