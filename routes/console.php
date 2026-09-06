<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-failed --hours=168')->daily();
Schedule::command('bluegate:sync-usage')->everyFiveMinutes()->withoutOverlapping();

Schedule::command('bluegate:check-nodes')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('bluegate:failover')->everyFiveMinutes()->withoutOverlapping();
