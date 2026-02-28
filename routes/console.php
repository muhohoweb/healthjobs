<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('subscriptions:expire', function () {
    \App\Models\Subscription::where('status', 'active')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
    $this->info('Expired subscriptions updated.');
})->purpose('Expire old subscriptions');