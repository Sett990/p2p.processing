<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => ['api-access-token']], function () {
    //common
    Route::get('payment-gateways', [\App\Http\Controllers\API\PaymentGatewayController::class, 'index']);
    Route::get('currencies', [\App\Http\Controllers\API\CurrencyController::class, 'index']);

    Route::group(['prefix' => 'payout'], function () {
        Route::get('offers', [\App\Http\Controllers\API\Payout\PayoutOfferController::class, 'index']);
    });

    Route::group(['prefix' => 'merchant'], function () {
        Route::get('order/{order:uuid}', [\App\Http\Controllers\API\Merchant\OrderController::class, 'show']);
        Route::post('order', [\App\Http\Controllers\API\Merchant\OrderController::class, 'store'])->name('api.order');
    });

    Route::group(['prefix' => 'h2h'], function () {
        Route::get('order/{order:uuid}', [\App\Http\Controllers\API\H2H\OrderController::class, 'show']);
        Route::post('order', [\App\Http\Controllers\API\H2H\OrderController::class, 'store']);
        Route::patch('order/{order:uuid}/cancel', [\App\Http\Controllers\API\H2H\OrderController::class, 'cancel']);

        //TODO
        //Route::patch('order/{order:uuid}/confirm-paid', [\App\Http\Controllers\API\H2H\OrderController::class, 'cancel']);

        Route::post('order/{order:uuid}/dispute', [\App\Http\Controllers\API\H2H\DisputeController::class, 'store'])->name('api.dispute');
        Route::get('order/{order:uuid}/dispute', [\App\Http\Controllers\API\H2H\DisputeController::class, 'show']);
    });
});

Route::post('app/sms', [\App\Http\Controllers\API\APP\SmsController::class, 'store'])->middleware(['idempotency']);
