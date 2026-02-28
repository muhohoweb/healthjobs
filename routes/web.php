<?php

use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HealthJobController;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RolesAndPermissionsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\welcomeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [welcomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Subscriptions
Route::middleware(['auth'])->group(function () {
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('subscriptions', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::patch('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

// M-Pesa callback — no auth, no CSRF
Route::post('mpesa/callback', [MpesaController::class, 'callback'])->name('mpesa.callback');

// Admin payments
Route::middleware(['auth', 'roles:super-admin'])->group(function () {
    Route::get('admin/payments', [MpesaController::class, 'index'])->name('admin.payments.index');
});

// Authenticated routes
Route::middleware(['auth', 'roles:super-admin'])->group(function () {
    Route::resource('admin/packages', PackageController::class)->names('admin.packages');
});

Route::middleware(['auth'])->group(function () {
    Route::controller(NotificationController::class)
        ->prefix('notifications')
        ->name('notifications.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::delete('/{uuid}', 'deleteNotifications')->name('deleteNotifications');
            Route::patch('{notification}/read', 'markAsRead')->name('read');
            Route::patch('mark-selected-read', 'markSelectedAsRead')->name('mark-selected-read');
            Route::patch('mark-all-read', 'markAllAsRead')->name('mark-all-read');
            Route::delete('delete-selected', 'deleteSelected')->name('delete-selected');
        });
});

Route::middleware(['auth', 'roles:super-admin'])->group(function () {
    Route::controller(FacilityController::class)
        ->prefix('facilities')
        ->name('facilities.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create')->middleware('permission:create-facility');
            Route::post('store', 'store')->name('store')->middleware('permission:create-facility');
        });
});


Route::get('test', [HealthJobController::class, 'test'])->name('test');

Route::post('whats-app-jobs',[HealthJobController::class,'storeFromWhatsApp'])->name('whats-app-jobs');
Route::post('whats-app-events', [EventsController::class, 'storeFromWhatsApp'])->name('whats-app-events');


Route::middleware(['auth','active-subscription'])->group(function () {
    Route::controller(HealthJobController::class)
        ->prefix('health-jobs')
        ->name('health-jobs.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create')->middleware('permission:create-job-postings');
            Route::post('upload', 'upload')->name('upload')->middleware('permission:create-job-postings');
            Route::post('jobs', 'store')->name('store');
            Route::get('{uuid}', 'show')->name('show');
            Route::post('interested', 'interested')->name('interested');
        });
});

Route::middleware(['auth'])->group(function () {
    Route::controller(FeedbackController::class)
        ->prefix('feedback')
        ->name('feedback.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('feedback.create');
            Route::post('/store', 'store')->name('feedback.store');
        });
});


Route::middleware(['auth'])->group(function () {
    Route::controller(EventsController::class)
        ->prefix('events')
        ->name('events.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('events.create');
            Route::post('/store', 'store')->name('events.store');
        });
});


Route::middleware(['auth'])->group(function () {
    Route::controller(\App\Http\Controllers\Settings\ProfileController::class)
        ->prefix('medics')
        ->name('medics.')
        ->group(function () {
            Route::get('/profiles', 'index')->name('medics.profiles');
        });
});



Route::middleware(['auth', 'roles:super-admin'])->group(function () {
    Route::controller(RolesAndPermissionsController::class)
        ->prefix('iam')
        ->name('iam.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::post('update', 'update')->name('roles.update');
            Route::get('roles', 'roles')->name('roles');
            Route::get('roles/map', 'map')->name('roles.map');
            Route::get('roles/create', 'createRole')->name('roles.create');
        });
});

Route::middleware(['auth', 'permission:has-complete-profile'])->group(function () {});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
