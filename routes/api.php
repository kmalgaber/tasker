<?php

use Illuminate\Support\Facades\Route;

Route::get('/api/public', function () {
    return response()->json(['The test is successful: GET@/api/test']);
});
Route::middleware('auth')->get('/api/protected', function () {
    return response()->json(['The test is successful: GET@/api/test']);
});
