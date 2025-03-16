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
        Route::get('/{payout:uuid}', [\App\Http\Controllers\API\Payout\PayoutController::class, 'show']);
        Route::post('/', [\App\Http\Controllers\API\Payout\PayoutController::class, 'store']);
    });

    Route::group(['prefix' => 'merchant'], function () {
        Route::get('order/{order:uuid}', [\App\Http\Controllers\API\Merchant\OrderController::class, 'show']);
        Route::post('order', [\App\Http\Controllers\API\Merchant\OrderController::class, 'store'])->name('api.order');
    });

    Route::group(['prefix' => 'h2h'], function () {
        Route::get('order/{order:uuid}', [\App\Http\Controllers\API\H2H\OrderController::class, 'show']);
        Route::post('order', [\App\Http\Controllers\API\H2H\OrderController::class, 'store']);
        Route::patch('order/{order:uuid}/cancel', [\App\Http\Controllers\API\H2H\OrderController::class, 'cancel']);
        Route::patch('order/{order:uuid}/finish', [\App\Http\Controllers\API\H2H\OrderController::class, 'finish']);

        //TODO
        //Route::patch('order/{order:uuid}/confirm-paid', [\App\Http\Controllers\API\H2H\OrderController::class, 'cancel']);

        Route::post('order/{order:uuid}/dispute', [\App\Http\Controllers\API\H2H\DisputeController::class, 'store'])->name('api.dispute');
        Route::get('order/{order:uuid}/dispute', [\App\Http\Controllers\API\H2H\DisputeController::class, 'show']);
    });

    Route::group(['prefix' => 'wallet'], function () {
        Route::get('balance', [\App\Http\Controllers\API\Merchant\WalletController::class, 'balance']);
        Route::post('withdraw', [\App\Http\Controllers\API\Merchant\WalletController::class, 'withdraw']);
    });
});

Route::group(['prefix' => 'bot', 'middleware' => ['api-bot-access-token']], function () {
    Route::get('order/{key}', [\App\Http\Controllers\API\Bot\BotController::class, 'index']);
    Route::post('order/{order:uuid}/dispute', [\App\Http\Controllers\API\Bot\BotController::class, 'storeDispute']);
    Route::post('order/{order:uuid}/dispute/accept', [\App\Http\Controllers\API\Bot\BotController::class, 'acceptDispute']);
    Route::post('order/{order:uuid}/dispute/cancel', [\App\Http\Controllers\API\Bot\BotController::class, 'cancelDispute']);
});

Route::group(['prefix' => 'deposit', 'middleware' => ['api-deposits-access-token']], function () {
    Route::post('webhook', [\App\Http\Controllers\API\Deposit\DepositController::class, 'webhook']);
});

Route::group(['prefix' => 'withdraw', 'middleware' => ['api-withdrawals-access-token']], function () {
    Route::post('webhook', [\App\Http\Controllers\API\Withdraw\WithdrawController::class, 'webhook']);
});

Route::group(['prefix' => 'app', 'middleware' => ['device-access-token']], function () {
    Route::post('sms', [\App\Http\Controllers\API\APP\SmsController::class, 'store']);
    Route::get('state', [\App\Http\Controllers\API\APP\StateController::class, 'index']);
    Route::post('device/connect', [\App\Http\Controllers\API\APP\DeviceController::class, 'connect']);
});
