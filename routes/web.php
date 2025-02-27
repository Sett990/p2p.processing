<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/payment/{order:uuid}', [\App\Http\Controllers\PaymentLinkController::class, 'show'])->name('payment.show');
Route::post('/payment/{order:uuid}/dispute', [\App\Http\Controllers\PaymentLinkController::class, 'storeDispute'])->name('payment.dispute.store');
Route::post('/payment/{order:uuid}/payment-detail/{paymentGateway}', [\App\Http\Controllers\PaymentLinkController::class, 'storePaymentDetail'])->name('payment.payment-detail.store');

// Выход из режима Impersonate
Route::post('/impersonate/leave', function () {
    if (auth()->user()->isImpersonated()) {
        auth()->user()->leaveImpersonation();
        return redirect()->route('admin.users.index');
    }
    return redirect()->back()->with('error', 'Вы не в режиме Impersonate');
})->middleware('auth', 'banned')->name('impersonate.leave');

Route::group(['middleware' => ['2fa']], function () {
    Route::group(['middleware' => ['auth', 'banned']], function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update.avatar');
        Route::patch('/profile/auth2fa', [ProfileController::class, 'updateAuth2fa'])->name('profile.update.auth2fa');
    });

    Route::group(['middleware' => ['auth', 'banned']], function () {
        Route::get('/', function () {

            if (auth()->user()->hasRole('Merchant')) {
                return redirect()->route('merchant.main.index');
            }

            if (auth()->user()->hasRole('Trader')) {
                return redirect()->route('trader.main.index');
            }

            return redirect()->route('admin.main.index');
            //return Inertia::render('Dashboard');
        })->name('dashboard');

        Route::post('/invoice', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoice.store');
        Route::patch('/user/online', [\App\Http\Controllers\UserOnlineController::class, 'toggle'])->name('user.online.toggle');
        Route::patch('/user/payout/online', [\App\Http\Controllers\UserOnlineController::class, 'payoutToggle'])->name('user.payout.online.toggle');
    });

    Route::group(['middleware' => ['auth', 'banned', 'role:Trader|Super Admin']], function () {
        Route::get('/trader/main', [\App\Http\Controllers\MainPageController::class, 'trader'])->name('trader.main.index');

        Route::patch('/payment-details/{paymentDetail}/toggle-active', [\App\Http\Controllers\PaymentDetailController::class, 'toggleActive'])->name('payment-details.toggle-active');
        Route::resource('/payment-details', \App\Http\Controllers\PaymentDetailController::class)->only(['index', 'create', 'store', 'edit', 'update']);

        //orders
        Route::resource('/orders', \App\Http\Controllers\OrderController::class)->only(['index']);
        Route::patch('/orders/{order}/accept', [\App\Http\Controllers\OrderController::class, 'acceptOrder'])->name('orders.accept');
        Route::patch('/orders/{order}/amount', [\App\Http\Controllers\Admin\OrderController::class, 'updateAmount'])->name('orders.update.amount');

        //statistics
        Route::get('trader/statistics', [\App\Http\Controllers\Trader\StatisticController::class, 'index'])->name('trader.statistics.index');

        //disputes
        Route::get('/disputes', [\App\Http\Controllers\DisputeController::class, 'index'])->name('disputes.index');
        Route::get('/disputes/{dispute}/receipt', [\App\Http\Controllers\DisputeController::class, 'receipt'])->name('disputes.receipt');
        Route::patch('/disputes/{dispute}/accept', [\App\Http\Controllers\DisputeController::class, 'accept'])->name('disputes.accept');
        Route::patch('/disputes/{dispute}/cancel', [\App\Http\Controllers\DisputeController::class, 'cancel'])->name('disputes.cancel');
        Route::patch('/disputes/{dispute}/rollback', [\App\Http\Controllers\DisputeController::class, 'rollback'])->name('disputes.rollback');

        //app
        Route::get('/apk', [\App\Http\Controllers\ApkController::class, 'index'])->name('apk.index');
        Route::get('/sms.apk', [\App\Http\Controllers\ApkController::class, 'download'])->name('app.download');

        Route::get('/finances', [\App\Http\Controllers\WalletController::class, 'index'])->name('wallet.index');

        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');

        Route::get('/sms-logs', [\App\Http\Controllers\SmsLogController::class, 'index'])->name('sms-logs.index');

        Route::any('auth/telegram/callback', [\App\Http\Controllers\Auth\SocialController::class, 'callback']);

        Route::get('/trader/payouts/offers', [\App\Http\Controllers\PayoutOfferController::class, 'create'])->name('trader.payout-offers.create');
        Route::get('/trader/payouts/offers/{payoutOffer}', [\App\Http\Controllers\PayoutOfferController::class, 'edit'])->name('trader.payout-offers.edit');
        Route::post('/trader/payouts/offers', [\App\Http\Controllers\PayoutOfferController::class, 'store'])->name('trader.payout-offers.store');
        Route::patch('/trader/payouts/offers/{payoutOffer}', [\App\Http\Controllers\PayoutOfferController::class, 'update'])->name('trader.payout-offers.update');
        Route::get('/trader/payouts', [\App\Http\Controllers\TraderPayoutController::class, 'index'])->name('trader.payouts.index');
        Route::get('/trader/payouts/{payout}', [\App\Http\Controllers\TraderPayoutController::class, 'show'])->name('trader.payouts.show');
        Route::post('/trader/payouts/{payout}/finish', [\App\Http\Controllers\TraderPayoutController::class, 'finish'])->name('trader.payouts.finish');
        Route::post('/trader/payouts/{payout}/refuse', [\App\Http\Controllers\TraderPayoutController::class, 'refuse'])->name('trader.payouts.refuse');

        //export
        Route::get('/trader/export/orders', [\App\Http\Controllers\Trader\ExportController::class, 'exportOrders'])->name('trader.export.orders');

        Route::get('/trader/settings', [\App\Http\Controllers\Trader\SettingController::class, 'index'])->name('trader.settings.index');
        Route::patch('/trader/settings', [\App\Http\Controllers\Trader\SettingController::class, 'update'])->name('trader.settings.update');
    });

    //common
    Route::group(['middleware' => ['auth', 'banned', 'role:Trader|Super Admin']], function () {
        Route::get('/modal/sms-logs/{user}', [\App\Http\Controllers\ModalController::class, 'smsLogs'])->name('modal.user.sms-logs');
    });

    Route::group(['middleware' => ['auth', 'banned', 'role:Merchant|Super Admin']], function () {
        Route::get('/merchant/main', [\App\Http\Controllers\MainPageController::class, 'merchant'])->name('merchant.main.index');

        Route::resource('/merchants', \App\Http\Controllers\MerchantController::class)->only(['index', 'show', 'create', 'store']);
        Route::patch('/merchants/{merchant}/callback', [\App\Http\Controllers\MerchantController::class, 'updateCallbackURL'])->name('merchants.callback.update');
        Route::patch('/merchants/{merchant}/gateway-settings', [\App\Http\Controllers\MerchantController::class, 'updateGatewaySettings'])->name('merchants.gateway-settings.update');

        Route::get('/integration', [\App\Http\Controllers\ApiIntegrationController::class, 'index'])->name('integration.index');

        Route::get('/merchant/finances', [\App\Http\Controllers\WalletController::class, 'index'])->name('merchant.finances.index');

        Route::resource('/payments', \App\Http\Controllers\PaymentController::class)->only(['index', 'create', 'store']);

        Route::resource('/payouts', \App\Http\Controllers\MerchantPayoutController::class)->only(['index']);
        Route::resource('/payout-gateways', \App\Http\Controllers\PayoutGatewayController::class)->only(['create', 'store', 'edit', 'update']);
    });

    Route::group(['prefix' => 'admin', 'as'=>'admin.', 'middleware' => ['auth', 'banned', 'role:Super Admin']], function () {
        Route::get('/main', [\App\Http\Controllers\MainPageController::class, 'admin'])->name('main.index');

        Route::patch('/users/{user}/toggle-online', [\App\Http\Controllers\Admin\UserController::class, 'toggleOnline'])->name('users.toggle-online');
        Route::resource('/users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::resource('/payment-gateways', \App\Http\Controllers\Admin\PaymentGatewayController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');

        Route::get('/deposits', [\App\Http\Controllers\Admin\DepositController::class, 'index'])->name('deposits.index');
        Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::get('/withdrawals/address/whitelist', [\App\Http\Controllers\Admin\AddressWhitelistController::class, 'index'])->name('withdrawals.address.whitelist.index');
        Route::patch('/withdrawals/{invoice}/success', [\App\Http\Controllers\Admin\WithdrawalController::class, 'success'])->name('withdrawals.success');
        Route::patch('/withdrawals/{invoice}/fail', [\App\Http\Controllers\Admin\WithdrawalController::class, 'fail'])->name('withdrawals.fail');

        Route::resource('/currencies', \App\Http\Controllers\Admin\CurrencyController::class)->only(['index']);
        Route::get('currencies/{currency}/price-parsers', [\App\Http\Controllers\Admin\PriceParserController::class, 'edit'])->name('currencies.price-parsers.edit');
        Route::patch('currencies/{currency}/price-parsers', [\App\Http\Controllers\Admin\PriceParserController::class, 'update'])->name('currencies.price-parsers.update');

        Route::get('/sms-logs', [\App\Http\Controllers\Admin\SmsLogController::class, 'index'])->name('sms-logs.index');
        Route::post('/sender-stop-list/{smsLog}', [\App\Http\Controllers\Admin\SenderStopListController::class, 'store'])->name('sender-stop-list.store');
        Route::delete('/sender-stop-list/{senderStopList}', [\App\Http\Controllers\Admin\SenderStopListController::class, 'destroy'])->name('sender-stop-list.destroy');

        Route::get('/payment-details', [\App\Http\Controllers\Admin\PaymentDetailController::class, 'index'])->name('payment-details.index');
        Route::resource('/payment-details', \App\Http\Controllers\PaymentDetailController::class)->only(['create', 'store', 'edit', 'update']);

        Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes.index');

        Route::get('/users/{user}/wallet', [\App\Http\Controllers\Admin\UserWalletController::class, 'index'])->name('users.wallet.index');
        Route::post('/users/{user}/wallet/deposit', [\App\Http\Controllers\Admin\UserWalletController::class, 'deposit'])->name('users.wallet.deposit');
        Route::post('/users/{user}/wallet/withdraw', [\App\Http\Controllers\Admin\UserWalletController::class, 'withdraw'])->name('users.wallet.withdraw');

        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings/update/prime-time-bonus', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePrimeTimeBonus'])->name('settings.update.prime-time-bonus');
        Route::patch('/settings/update/support-link', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSupportLink'])->name('settings.update.support-link');
        Route::patch('/settings/update/funds-on-hold', [\App\Http\Controllers\Admin\SettingsController::class, 'updateFundsOnHold'])->name('settings.update.funds-on-hold');

        Route::resource('/notifications', \App\Http\Controllers\Admin\NotificationController::class)->only('index', 'store');

        Route::get('/merchants', [\App\Http\Controllers\Admin\MerchantController::class, 'index'])->name('merchants.index');
        Route::get('/merchants/{merchant}', [\App\Http\Controllers\MerchantController::class, 'show'])->name('merchants.show');
        Route::patch('/merchants/{merchant}/ban', [\App\Http\Controllers\Admin\MerchantController::class, 'ban'])->name('merchants.ban');
        Route::patch('/merchants/{merchant}/unban', [\App\Http\Controllers\Admin\MerchantController::class, 'unban'])->name('merchants.unban');
        Route::patch('/merchants/{merchant}/validated', [\App\Http\Controllers\Admin\MerchantController::class, 'validated'])->name('merchants.validated');
        Route::patch('/merchants/{merchant}/settings', [\App\Http\Controllers\Admin\MerchantController::class, 'updateSettings'])->name('merchants.settings.update');

        Route::get('/payouts/{payout}/receipt', [\App\Http\Controllers\Admin\PayoutController::class, 'receipt'])->name('payouts.receipt');
        Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
        Route::get('/payouts/{payout}', [\App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('payouts.show');
        Route::post('/payouts/{payout}/finish', [\App\Http\Controllers\Admin\PayoutController::class, 'finish'])->name('payouts.finish');
        Route::post('/payouts/{payout}/cancel', [\App\Http\Controllers\Admin\PayoutController::class, 'cancel'])->name('payouts.cancel');
        Route::post('/payouts/{payout}/pass-to-trader', [\App\Http\Controllers\Admin\PayoutController::class, 'passToTrader'])->name('payouts.pass-to-trader');

        // Вход под другим пользователем
        Route::post('/impersonate/{user}', function (\App\Models\User $user) {
            if (auth()->user()->canImpersonate()) {
                auth()->user()->impersonate($user);

                if ($user->google2fa_secret) {
                    session()->put('user_2fa_passed', true);
                }

                return redirect()->route('dashboard');
            }
            return redirect()->back()->with('error', 'Нет прав для входа под другим пользователем');
        })->name('impersonate.start');
    });
});

Route::any('/telegram-bot/{token}/webhook', [\App\Http\Controllers\TelegramBotWebhookController::class, 'store'])->name('telegram-bot.webhook');

require __DIR__.'/auth.php';
