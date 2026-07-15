<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('crm:dispatch-follow-up-reminders')
    ->everyFifteenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('activitylog:clean --force')
    ->dailyAt('02:15')
    ->onOneServer()
    ->withoutOverlapping();
