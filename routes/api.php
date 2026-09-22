<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\FundraisingController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LeadCallController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\RoleController;
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
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyPasswordResetOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPasswordWithOtp']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/secondary-email', [AuthController::class, 'updateSecondaryEmail']);
        Route::post('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
        Route::post('/avatar/remove', [AuthController::class, 'removeAvatar']);
        Route::post('/password', [AuthController::class, 'updatePassword']);
    });
});

// Zoho CRM Lead Generation & Journey Engine
Route::apiResource('leads', LeadController::class);
Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
Route::apiResource('lead-calls', LeadCallController::class)->only(['index', 'store', 'destroy']);
Route::apiResource('contacts', ContactController::class);

// Quotations Engine (Email & WhatsApp Dispatch + Invoice Upgrade)
Route::apiResource('quotes', QuoteController::class);
Route::post('quotes/{quote}/send-email', [QuoteController::class, 'sendEmail']);
Route::get('quotes/{quote}/whatsapp', [QuoteController::class, 'getWhatsAppLink']);
Route::post('quotes/{quote}/upgrade-invoice', [QuoteController::class, 'upgradeToInvoice']);

// Documents Repository (Contracts, Proposals, Brand Guides, Grants)
Route::apiResource('documents', DocumentController::class);
Route::get('documents/{document}/download', [DocumentController::class, 'download']);

// Fundraising & Impact Grants Tracker
Route::post('fundraising/import', [FundraisingController::class, 'importCsv']);
Route::apiResource('fundraising', FundraisingController::class);

// Public / Token-enabled CRUD Resources
Route::apiResource('clients', ClientController::class);
Route::apiResource('projects', ProjectController::class);

Route::apiResource('pipeline', DealController::class);
Route::apiResource('deals', DealController::class);
Route::post('pipeline/{deal}/win', [DealController::class, 'win']);
Route::post('deals/{deal}/win', [DealController::class, 'win']);

Route::apiResource('tasks', TaskController::class);
Route::post('tasks/{task}/links', [TaskController::class, 'addLink']);
Route::delete('tasks/{task}/links/{linkId}', [TaskController::class, 'removeLink']);
Route::post('tasks/{task}/comments', [TaskController::class, 'addComment']);
Route::post('tasks/{task}/workflow', [TaskController::class, 'workflowAction']);

Route::get('invoices/next-number', [InvoiceController::class, 'nextNumber']);
Route::apiResource('invoices', InvoiceController::class);
Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay']);
Route::post('invoices/{invoice}/send-reminder', [InvoiceController::class, 'sendReminder']);

Route::post('expenses/upload-receipt', [ExpenseController::class, 'uploadReceipt']);
Route::apiResource('expenses', ExpenseController::class);

Route::get('finance/overview', [FinanceController::class, 'overview']);
Route::apiResource('users', UserController::class);
Route::post('users/{user}', [UserController::class, 'update']);
Route::post('users/{user}/resend-invitation', [UserController::class, 'resendInvitation']);
Route::post('users/{user}/permissions', [RoleController::class, 'assignUserPermissions']);
Route::apiResource('roles', RoleController::class);
Route::post('roles/{role}', [RoleController::class, 'update']);
Route::get('roles-permissions/catalog', [RoleController::class, 'permissionsCatalog']);
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

// Audit Trail & System Activity (Admin & IT Manager)
Route::get('audit-logs', [AuditLogController::class, 'index']);
Route::post('audit-logs', [AuditLogController::class, 'store']);

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
Route::get('notifications', [NotificationController::class, 'index']);
Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
Route::post('notifications/{notification}/unread', [NotificationController::class, 'markUnread']);
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
