<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SystemUpgradeController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| JMOS RESTful API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Public / Token-enabled CRUD Resources
Route::apiResource('clients', ClientController::class);
Route::apiResource('projects', ProjectController::class);

Route::apiResource('pipeline', DealController::class);
Route::apiResource('deals', DealController::class);
Route::post('pipeline/{deal}/win', [DealController::class, 'win']);
Route::post('deals/{deal}/win', [DealController::class, 'win']);

Route::apiResource('tasks', TaskController::class);

Route::apiResource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay']);

Route::apiResource('expenses', ExpenseController::class);

Route::get('finance/overview', [FinanceController::class, 'overview']);
Route::apiResource('users', UserController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('services', ServiceController::class)->only(['index', 'store', 'destroy']);

// System Settings & SMTP (Super Admin / IT)
Route::get('settings', [SettingsController::class, 'index']);
Route::post('settings', [SettingsController::class, 'update']);
Route::post('settings/test-email', [SettingsController::class, 'testEmail']);
Route::post('settings/test-calendar', [SettingsController::class, 'testCalendar']);

// System Software Upgrade & IT Maintenance (Super Admin & IT)
Route::prefix('system')->group(function () {
    Route::get('status', [SystemUpgradeController::class, 'status']);
    Route::post('upgrade', [SystemUpgradeController::class, 'upgrade']);
    Route::post('migrate', [SystemUpgradeController::class, 'migrate']);
    Route::post('backup', [SystemUpgradeController::class, 'createBackup']);
    Route::get('backups', [SystemUpgradeController::class, 'backups']);
    Route::get('backups/{filename}', [SystemUpgradeController::class, 'downloadBackup']);
    Route::post('clear-cache', [SystemUpgradeController::class, 'clearCache']);
});

// Operations & Meetings Calendar
Route::get('calendar/events', [CalendarController::class, 'index']);
Route::post('calendar/events', [CalendarController::class, 'store']);
Route::post('calendar/events/{event}/meet', [CalendarController::class, 'generateMeet']);
Route::delete('calendar/events/{event}', [CalendarController::class, 'destroy']);
Route::get('calendar/sync-status', [CalendarController::class, 'syncStatus']);
Route::post('calendar/sync', [CalendarController::class, 'sync']);
Route::post('calendar/disconnect', [CalendarController::class, 'disconnect']);

// Team & 1-on-1 Chat Hub
Route::get('chat/threads', [ChatController::class, 'index']);
Route::post('chat/threads/direct', [ChatController::class, 'getDirectThread']);
Route::post('chat/threads', [ChatController::class, 'storeGroupThread']);
Route::get('chat/threads/{thread}/messages', [ChatController::class, 'getMessages']);
Route::post('chat/threads/{thread}/messages', [ChatController::class, 'sendMessage']);
Route::post('chat/threads/{thread}/read', [ChatController::class, 'markRead']);
Route::get('chat/unread-count', [ChatController::class, 'unreadCount']);
Route::post('chat/upload', [ChatController::class, 'uploadAttachment']);

// Notifications & Operational Alerts
Route::middleware('auth:sanctum')->group(function () {
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/{notification}/unread', [NotificationController::class, 'markUnread']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
});
