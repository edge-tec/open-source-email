<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
*/

Schedule::command('edgemail:calculate-storage')->everyFiveMinutes();
Schedule::command('edgemail:cleanup-logs --days=30')->dailyAt('02:00');
Schedule::command('queue:prune-batches --hours=48')->daily();
