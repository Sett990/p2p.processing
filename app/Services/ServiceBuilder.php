<?php

namespace App\Services;

use App\Contracts\CommissionServiceContract;
use App\Contracts\DisputeServiceContract;
use App\Contracts\InvoiceServiceContract;
use App\Contracts\MarketServiceContract;
use App\Contracts\OrderCallbackServiceContract;
use App\Contracts\OrderServiceContract;
use App\Contracts\PayoutServiceContract;
use App\Contracts\ServiceBuilderContract;
use App\Contracts\SettingsServiceContract;
use App\Contracts\SmsServiceContract;
use App\Contracts\TelegramBotServiceContract;
use App\Contracts\WalletServiceContract;

class ServiceBuilder implements ServiceBuilderContract
{
    public function order(): OrderServiceContract
    {
        return make(OrderServiceContract::class);
    }

    public function sms(): SmsServiceContract
    {
        return make(SmsServiceContract::class);
    }

    public function orderCallback(): OrderCallbackServiceContract
    {
        return make(OrderCallbackServiceContract::class);
    }

    public function market(): MarketServiceContract
    {
        return make(MarketServiceContract::class);
    }

    public function dispute(): DisputeServiceContract
    {
        return make(DisputeServiceContract::class);
    }

    public function wallet(): WalletServiceContract
    {
        return make(WalletServiceContract::class);
    }

    public function invoice(): InvoiceServiceContract
    {
        return make(InvoiceServiceContract::class);
    }

    public function settings(): SettingsServiceContract
    {
        return make(SettingsServiceContract::class);
    }

    public function telegramBot(): TelegramBotServiceContract
    {
        return make(TelegramBotServiceContract::class);
    }

    public function payout(): PayoutServiceContract
    {
        return make(PayoutServiceContract::class);
    }

    public function commission(): CommissionServiceContract
    {
        return make(CommissionServiceContract::class);
    }
}
