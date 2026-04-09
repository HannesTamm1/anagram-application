<?php

use App\Http\Controllers\Api\AnagramController;
use App\Http\Controllers\Api\WordController;
use App\Http\Controllers\Api\WordImportController;
use Illuminate\Support\Facades\Route;

Route::get('/words', WordController::class);
Route::post('/words/import', WordImportController::class);
Route::get('/anagrams', AnagramController::class);

Route::get('/message', function () {
    return response()->json([
        'message' => 'Hello from Laravel backend',
        'status' => 'ok',
    ]);
});
