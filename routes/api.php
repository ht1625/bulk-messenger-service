<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MessageController;

Route::prefix('v1')->group(function () {
    Route::get('/messages/sent', [MessageController::class, 'sent']);
});
