<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/budget-calculator', function () {
    return view('pages.budget-calculator');
});

// Public Client Quotation Review & Approval Web Routes
Route::get('/quotes/view/{quoteNumber}', [QuoteController::class, 'publicView'])->name('quotes.public.view');
Route::post('/quotes/approve/{quoteNumber}', [QuoteController::class, 'publicApprove'])->name('quotes.public.approve');

// Google Calendar OAuth Web Redirect & Callback
Route::get('/calendar/google/redirect', [CalendarController::class, 'googleRedirect'])->name('calendar.google.redirect');
Route::get('/calendar/google/callback', [CalendarController::class, 'googleCallback'])->name('calendar.google.callback');

// Single Page Application (SPA) Catch-all Route
// Resolves direct paths (e.g. /login, /projects, /tasks, /leads, /finance, /quotes, /chat, etc.) from notifications & deep links
Route::get('/{any?}', function () {
    return view('jmos');
})->where('any', '.*');
