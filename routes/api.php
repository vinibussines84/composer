<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PixController;
use App\Http\Controllers\Api\PixWebhookController;
use App\Http\Controllers\Api\WithdrawController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Route::post('/pix', [PixController::class, 'handle']);

//Route::post('/pix/webhook', [PixWebhookController::class, 'handle']);

Route::post('/webhook/bloobank', [\App\Http\Controllers\Webhook\BloobankWebhookController::class, 'handle']);

Route::post('/pix/status', [PixController::class, 'status']);

Route::post('/withdraw', [WithdrawController::class, 'handleWithdraw']);

