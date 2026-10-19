<?php

use Illuminate\Support\Facades\Schedule;

/*
| Scheduled tasks. On the server, one cron entry runs the scheduler every minute:
|   * * * * * cd /home/USER/academy && php artisan schedule:run >> /dev/null 2>&1
| (see docs/DEPLOYMENT_GO54.md). Nothing here is required for the site to work: exam expiry,
| payment confirmation and certificate issuing all happen during normal requests, and expired
| database sessions are removed by Laravel's built-in session lottery.
*/

// Remove expired password-reset tokens.
Schedule::command('auth:clear-resets')->daily();
