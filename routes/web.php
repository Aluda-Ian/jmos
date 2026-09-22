<?php

use App\Http\Controllers\Api\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/budget-calculator', function () {
    return view('pages.budget-calculator');
});

// Google Calendar OAuth Web Redirect & Callback
Route::get('/calendar/google/redirect', [CalendarController::class, 'googleRedirect'])->name('calendar.google.redirect');
Route::get('/calendar/google/callback', [CalendarController::class, 'googleCallback'])->name('calendar.google.callback');

// Single Page Application (SPA) Catch-all Route
// Resolves direct paths (e.g. /login, /projects, /tasks, /leads, /finance, /chat, etc.) from notifications & deep links
Route::get('/{any?}', function () {
    return view('jmos');
})->where('any', '.*');
