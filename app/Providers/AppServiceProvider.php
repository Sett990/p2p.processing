<?php

namespace App\Providers;

use App\Contracts\DisputeServiceContract;
use App\Contracts\InvoiceServiceContract;
use App\Contracts\MarketServiceContract;
use App\Contracts\OrderCallbackServiceContract;
use App\Contracts\OrderServiceContract;
use App\Contracts\QueriesBuilderContract;
use App\Contracts\ServiceBuilderContract;
use App\Contracts\SettingsServiceContract;
use App\Contracts\SmsServiceContract;
use App\Contracts\TelegramBotServiceContract;
use App\Contracts\WalletServiceContract;
use App\Mixins\ResponseMixins;
use App\Models\Dispute;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Queries\Eloquent\DisputeQueriesEloquent;
use App\Queries\Eloquent\InvoiceQueriesEloquent;
use App\Queries\Eloquent\MerchantQueriesEloquent;
use App\Queries\Eloquent\OrderQueriesEloquent;
use App\Queries\Eloquent\PaymentDetailQueriesEloquent;
use App\Queries\Eloquent\PaymentGatewayQueriesEloquent;
use App\Queries\Interfaces\DisputeQueries;
use App\Queries\Interfaces\InvoiceQueries;
use App\Queries\Interfaces\MerchantQueries;
use App\Queries\Interfaces\OrderQueries;
use App\Queries\Interfaces\PaymentDetailQueries;
use App\Queries\Interfaces\PaymentGatewayQueries;
use App\Queries\QueriesBuilder;
use App\Services\Dispute\DisputeService;
use App\Services\Invoice\InvoiceService;
use App\Services\Market\MarketService;
use App\Services\Order\OrderService;
use App\Services\OrderCallback\OrderCallbackService;
use App\Services\ServiceBuilder;
use App\Services\Settings\SettingsService;
use App\Services\Sms\SmsService;
use App\Services\TelegramBot\TelegramBotService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //services
        $this->app->singleton(ServiceBuilderContract::class, function () {
            return new ServiceBuilder();
        });
        $this->app->bind(OrderServiceContract::class, function () {
            return new OrderService();
        });
        $this->app->bind(SmsServiceContract::class, function () {
            return new SmsService();
        });
        $this->app->bind(OrderCallbackServiceContract::class, function () {
            return new OrderCallbackService();
        });
        $this->app->singleton(MarketServiceContract::class, function () {
            return new MarketService();
        });
        $this->app->singleton(DisputeServiceContract::class, function () {
            return new DisputeService();
        });
        $this->app->singleton(WalletServiceContract::class, function () {
            return new WalletService();
        });
        $this->app->singleton(InvoiceServiceContract::class, function () {
            return new InvoiceService();
        });
        $this->app->singleton(SettingsServiceContract::class, function () {
            return new SettingsService();
        });
        $this->app->singleton(TelegramBotServiceContract::class, function () {
            return new TelegramBotService(
                config('telegram.bots.mybot.webhook_token')
            );
        });

        //queries
        $this->app->singleton(QueriesBuilderContract::class, function () {
            return new QueriesBuilder();
        });
        $this->app->bind(OrderQueries::class, function () {
            return new OrderQueriesEloquent();
        });
        $this->app->bind(PaymentGatewayQueries::class, function () {
            return new PaymentGatewayQueriesEloquent();
        });
        $this->app->bind(PaymentDetailQueries::class, function () {
            return new PaymentDetailQueriesEloquent();
        });
        $this->app->bind(DisputeQueries::class, function () {
            return new DisputeQueriesEloquent();
        });
        $this->app->bind(MerchantQueries::class, function () {
            return new MerchantQueriesEloquent();
        });
        $this->app->bind(InvoiceQueries::class, function () {
            return new InvoiceQueriesEloquent();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::mixin(new ResponseMixins());

        Gate::define('access-to-payment-detail', function (User $user, PaymentDetail $paymentDetail) {
            return $user->id === $paymentDetail->user_id || $user->hasRole('Super Admin');
        });
        Gate::define('access-to-order', function (User $user, Order $order) {
            return $user->id === $order->paymentDetail?->user_id || $user->id === $order->merchant->user_id || $user->hasRole('Super Admin');
        });
        Gate::define('access-to-merchant', function (User $user, Merchant $merchant) {
            return $user->id === $merchant->user_id || $user->hasRole('Super Admin');
        });
        Gate::define('access-to-dispute', function (User $user, Dispute $dispute) {
            return $user->id === optional($dispute->order->paymentDetail)->user_id || $user->hasRole('Super Admin');
        });
        Gate::define('access-to-dispute-receipt', function (User $user, Dispute $dispute) {
            return $user->id === optional($dispute->order->paymentDetail)->user_id || $user->hasRole('Super Admin');
        });
        Gate::define('access-to-self', function (User $user) {
            return $user->id === auth()->id() || $user->hasRole('Super Admin');
        });

        //Socialite
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('telegram', \SocialiteProviders\Telegram\Provider::class);
        });

        Route::bind('order', function($id, \Illuminate\Routing\Route $route) {
            if ($route->bindingFieldFor('order') === 'uuid') {
                return Order::withoutGlobalScopes()->where('uuid', $id)->firstOrFail();
            }

            return Order::findOrFail($id);
        });
    }
}
