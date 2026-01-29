<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::redirect('/', '/login');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});