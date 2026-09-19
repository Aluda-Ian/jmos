<?php

use Illuminate\Support\Facades\Route;

Route::get('/budget-calculator', function () {
    return view('pages.budget-calculator');
});

// Single Page Application (SPA) Catch-all Route
// Resolves direct paths (e.g. /login, /projects, /tasks, /leads, /finance, /chat, etc.) from notifications & deep links
Route::get('/{any?}', function () {
    return view('jmos');
})->where('any', '.*');
