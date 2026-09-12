<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('jmos');
});

Route::get('/budget-calculator', function () {
    return view('pages.budget-calculator');
});
