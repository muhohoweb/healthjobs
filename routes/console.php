<?php

use App\Models\Subscription;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
    Subscription::where('status', 'active')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
})->hourly()->name('expire-subscriptions');
