<?php

use Illuminate\Support\Facades\Route;

Route::get('/message', function () {
    return response()->json([
        'message' => 'Hello from Laravel backend',
        'status' => 'ok',
    ]);
});
