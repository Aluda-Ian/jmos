<?php

use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/budget-calculator', function () {
    return view('pages.budget-calculator');
});

// Public Client Quotation Review & Approval Web Routes
Route::get('/quotes/view/{quoteNumber}', [QuoteController::class, 'publicView'])->name('quotes.public.view');
Route::post('/quotes/approve/{quoteNumber}', [QuoteController::class, 'publicApprove'])->name('quotes.public.approve');

// Public Client Contract Review & E-Signature (unguessable token link sent by email / WhatsApp)
Route::get('/contracts/sign/{token}', [ContractController::class, 'publicShow'])->name('contracts.public.show');
Route::post('/contracts/sign/{token}', [ContractController::class, 'publicSign'])
    ->middleware('throttle:10,1')
    ->name('contracts.public.sign');

// Public Client Invoice View (signed link sent by email / WhatsApp)
Route::get('/invoices/view/{invoiceNo}', [InvoiceController::class, 'publicView'])
    ->middleware('signed:relative')
    ->name('invoices.public.view');

// Google Calendar OAuth Web Redirect & Callback
Route::get('/calendar/google/redirect', [CalendarController::class, 'googleRedirect'])->name('calendar.google.redirect');
Route::get('/calendar/google/callback', [CalendarController::class, 'googleCallback'])->name('calendar.google.callback');

// Single Page Application (SPA) Catch-all Route
// Resolves direct paths (e.g. /login, /projects, /tasks, /leads, /finance, /quotes, /chat, etc.) from notifications & deep links
Route::get('/{any?}', function () {
    return view('jmos');
})->where('any', '.*');
