<?php

namespace App\Contracts;

interface ServiceBuilderContract
{
    public function order(): OrderServiceContract;

    public function sms(): SmsServiceContract;

    public function orderCallback(): OrderCallbackServiceContract;

    public function market(): MarketServiceContract;

    public function dispute(): DisputeServiceContract;

    public function wallet(): WalletServiceContract;

    public function invoice(): InvoiceServiceContract;

    public function settings(): SettingsServiceContract;

    public function telegramBot(): TelegramBotServiceContract;

    public function payout(): PayoutServiceContract;
}
