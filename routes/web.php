<?php

use App\Http\Controllers\ProfileController;
use App\Models\SmsParser;
use App\Services\Sms\Utils\Parser;
use Illuminate\Support\Facades\Route;

Route::get('/payment/{order:uuid}', [\App\Http\Controllers\PaymentLinkController::class, 'show'])->name('payment.show');
Route::post('/payment/{order:uuid}/dispute', [\App\Http\Controllers\PaymentLinkController::class, 'storeDispute'])->name('payment.dispute.store');
Route::post('/payment/{order:uuid}/payment-detail/{paymentGateway}', [\App\Http\Controllers\PaymentLinkController::class, 'storePaymentDetail'])->name('payment.payment-detail.store');

Route::group(['middleware' => ['auth', 'banned']], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::group(['middleware' => ['auth', 'banned']], function () {
    Route::get('/', function () {

        if (auth()->user()->hasRole('Merchant')) {
            return redirect()->route('merchants.index');
        }

        return redirect()->route('payment-details.index');
        //return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::post('/invoice', [\App\Http\Controllers\InvoiceController::class, 'store'])->name('invoice.store');
    Route::patch('/user/online', [\App\Http\Controllers\UserOnlineController::class, 'toggle'])->name('user.online.toggle');
    Route::patch('/user/payout/online', [\App\Http\Controllers\UserOnlineController::class, 'payoutToggle'])->name('user.payout.online.toggle');
});

Route::group(['middleware' => ['auth', 'banned', 'role:Trader|Super Admin']], function () {
    Route::resource('/payment-details', \App\Http\Controllers\PaymentDetailController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    //orders
    Route::resource('/orders', \App\Http\Controllers\OrderController::class)->only(['index']);
    Route::patch('/orders/{order}/accept', [\App\Http\Controllers\OrderController::class, 'acceptOrder'])->name('orders.accept');

    //disputes
    Route::get('/disputes', [\App\Http\Controllers\DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}/receipt', [\App\Http\Controllers\DisputeController::class, 'receipt'])->name('disputes.receipt');
    Route::patch('/disputes/{dispute}/accept', [\App\Http\Controllers\DisputeController::class, 'accept'])->name('disputes.accept');
    Route::patch('/disputes/{dispute}/cancel', [\App\Http\Controllers\DisputeController::class, 'cancel'])->name('disputes.cancel');
    Route::patch('/disputes/{dispute}/rollback', [\App\Http\Controllers\DisputeController::class, 'rollback'])->name('disputes.rollback')->middleware('role:Super Admin');

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
});

//common
Route::group(['middleware' => ['auth', 'banned', 'role:Trader|Super Admin']], function () {
    Route::get('/modal/sms-logs/{user}', [\App\Http\Controllers\ModalController::class, 'smsLogs'])->name('modal.user.sms-logs');
});

Route::group(['middleware' => ['auth', 'banned', 'role:Merchant|Super Admin']], function () {
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
    Route::resource('/users', \App\Http\Controllers\Admin\UserController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::resource('/payment-gateways', \App\Http\Controllers\Admin\PaymentGatewayController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders.index');

    Route::get('/withdrawals', [\App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::patch('/withdrawals/{invoice}/success', [\App\Http\Controllers\Admin\WithdrawalController::class, 'success'])->name('withdrawals.success');
    Route::patch('/withdrawals/{invoice}/fail', [\App\Http\Controllers\Admin\WithdrawalController::class, 'fail'])->name('withdrawals.fail');

    Route::resource('/currencies', \App\Http\Controllers\Admin\CurrencyController::class)->only(['index']);
    Route::get('currencies/{currency}/price-parsers', [\App\Http\Controllers\Admin\PriceParserController::class, 'edit'])->name('currencies.price-parsers.edit');
    Route::patch('currencies/{currency}/price-parsers', [\App\Http\Controllers\Admin\PriceParserController::class, 'update'])->name('currencies.price-parsers.update');

    Route::resource('/sms-parsers', \App\Http\Controllers\Admin\SmsParserController::class)->except(['show']);
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

    Route::get('/payouts/{payout}/receipt', [\App\Http\Controllers\Admin\PayoutController::class, 'receipt'])->name('payouts.receipt');
    Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::get('/payouts/{payout}', [\App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('payouts.show');
    Route::post('/payouts/{payout}/finish', [\App\Http\Controllers\Admin\PayoutController::class, 'finish'])->name('payouts.finish');
    Route::post('/payouts/{payout}/cancel', [\App\Http\Controllers\Admin\PayoutController::class, 'cancel'])->name('payouts.cancel');
    Route::post('/payouts/{payout}/pass-to-trader', [\App\Http\Controllers\Admin\PayoutController::class, 'passToTrader'])->name('payouts.pass-to-trader');
});

Route::any('/telegram-bot/{token}/webhook', [\App\Http\Controllers\TelegramBotWebhookController::class, 'store'])->name('telegram-bot.webhook');

Route::get('test', function () {
    /**
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\SmsParser> $parsers
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $gateways
     * @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\PaymentGateway> $gatewaysAll
     */
    $parsers = \App\Models\SmsParser::all();
    $gateways = \App\Models\PaymentGateway::query()
        ->whereHas('smsParsers')
        ->get();
    $gatewaysAll = \App\Models\PaymentGateway::query()->get();

   /* dump('Всего парсеров ' . $parsers->count());
    dump('Всего банков с парсерами ' . $gateways->count());
    dump('Всего банков ' . $gatewaysAll->count());
    dump('');

    $foundedParsers = collect([]);

    foreach ($parsers as $key => $parser) {
        $amount = parseMessage($parser->format);

        if ($amount) {

        } else {
            dump(normalizeMessage($parser->format) . ' ' . $parser->id);
        }
    }

    dump('Найдено парсеров ' . $foundedParsers->count());*/

    $count = 0;
    $founded = [];

    $stopWords = [
        'оставьте',
        'товары',
        'яндекс',
        'списание',
        'hopefully',
        'покупка',
        'будильник',
        'хотим',
        'подпиской',
        'услуге',
        'тариф',
        'наличные',
        'кэшбэк',
        'бесплатно',
        'недостаточно',
        'стриме',
        'вход',
        'device',
        'торговый',
        'вырос',
        'не смогли',
        'обратите внимание',
        'подтверждения',
        'сообщение',
        'взбодриться',
        'обновляется',
        'израсходовали',
        'введите код',
        'код ',
        ' код',
        'по постановлению судебного пристава о наложении ареста по',
        'снимет блокировку, чтобы вы снова могли',
        'Подтвердите перевод в другой банк',
        'Perevod s karty',
        'Это Ева, виртуальный помощник',
        'дноразовый код для доступа',
        'У нас отличная новость',
        'Этот абонент оставил Вам',
        'Отказ - недостаточно',
        'Никому не говорите код',
        'Внесите на счёт мобильного',
        'Наличные',
        'Код для входа в Альфа-Онлайн',
        'VYDACHA NALICHNYH',
        'лимит по карте',
        'vyidacha nalichnyih',
        'превышен лимит на операцию',
        'Списан перевод',
        'в салоне вы можете',
        'покупка',
        'списание',
        'кешбэк',
        'пополнить счет',
        'Оплата',
        'Звонили',
        'заявка',
        'Напоминаем',
        'Попробуйте',
        'nikomu ne soobshhajte',
        'ограничены',
        'звонил',
        'Check',
        'Оплачивайте',
        'Вход в СберБанк',
        'позвоните',
        'Ваш баланс меньше нуля',
        'Вход в',
        'заблокирована',
        'поделитесь мнением',
        'пароль',
        'блокировку',
        'заблокированы',
        'маркетплейс',
        ' kod ',
        ' kod:',
        'ne govorite',
        'не говорите',
        'Здравствуйте',
        'missed',
        'Покупка',
        'заказ',
        'Ваша карта',
        'Приходите',
        'Служба качества',
        'приветствует',
        'Снятие',
        'скидка',
        'скидку',
        'для входа',
        'Никому не сообщайте',
        'Подтвердить',
        'голосовых',
        'заблокирован',
        'Платите ',
        'погашения',
        'Чек билайна',
        'Подтвердите',
        'код:',
        'Код:',
        'Сервис',
        'Спасибо',
        'pin-kod',
        'soobshhajte',
        'Podtverdite',
        'Сегодня',
        'встреча',
        'получите',
        'сбережения',
        'Доступна кредитка',
        'Snyatie',
        'zablokirovana',
        'снятие',
        'Скачивайте',
        'Внесена',
        'отключен',
        'uvedomlenij',
        'снято',
        'Для оплаты',
        'Выдача',
        'Чек по',
        'ПИН-код',
        'дозвониться',
        'одобрено',
        'тариф',
        'spisanie',
        'устройство',
        'код доступа',
        'вход',
        'годовых',
        'заем',
        'кредит',
        'voshli',
        'отклонена',
        'телефона',
        'потерялись',
        'расход',
        'изменен',
        'sekretnogo',
        'Автоплатёж',
        'неверный',
        'Управляйте',
        'покупка',
        'оплата',
        'Доступен',
        'можно',
        'есть',
        'зайдите',
        'списано',
        'выдача',
        'со счёта',
        'уведомлений',
        'уведомления',
        'сообщайте',
        'подробнее',
        'абонент',
        'заём',
        'verification',
        'никому',
        'чтобы',
        'пополните счет',
        'код ',
        'Изменение',
        'Займ ',
        'Изменение ',
        'Установите ',
        'Негде ',
        'задолженность',
        'Гарантировано',
        'NEDOSTATOCHNO',
        'мнение',
        'Необходимо',
        'Кешбэк',
        'Данные',
        'безопасности',
        'updating',
        'сделал',
        'новый',
        'обновляем',
        'обновление',
        'Informiruem',
        'Nedostatochno',
        'займы',
        'Служба',
        'Выписка',
        'звонок',
        'посмотреть',
        'дела',
        'привет',
        'перевести',
        'сообщений',
        'телефоном',
        'концентрации',
        'Одобрили',
        'новая',
        'обновили',
        'Снимайте',
        'маршрута',
        'ниже',
        'Vhod',
        'Заявление',
        'недостаточно',
        'Andropova',
        'Платеж',
        'Поступил платёж',
        'сообщения',
    ];

    foreach (\App\Models\SmsLog::all() as $smsLog) {
        $message = normalizeMessage($smsLog->message);
        $amount = parseMessage($smsLog->message);
        if ($amount) {
            $founded[] = normalizeMessage($smsLog->message);
            $founded[] = $amount;
            $count++;
        }/* else {
            $stop = false;
            foreach ($stopWords as $stopWord) {
                $regex = '/' . $stopWord . '/mi';
                preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

                if (! empty($matches[0])) {
                    $stop = true;
                    break;
                }
            }
            if (! $stop) {
                $amount = findAmount($message);
                if ($amount) {
                    $founded[] = $message . ' ' . $smsLog->sender . ' ' . $smsLog->id;
                }
            }
        }*/
    }

    dump($count);
    dd($founded);
});

function parseMessage($message): ?string
{
    $triggerPatterns = [
        'перевод\s(?<amount>\d+(.\d+){0,3})р\sот\s.+\sбаланс',
        'перевод\sна\sсумму\s.+\sиз\s.+\sот\s',
        'perevod\s.+\sot\s.+\siz\s.+\sna\sschet\s',
        'зачислен перевод по',
        'поступление',
        'пополнение',
        'зачисление',
        '[а-я]+\sпополнена',
        'popolnenie scheta',
        'postuplenie sredstv na schet',
        'postuplenie',
        'получен перевод',
        'popolnenie',
        'приход на карту',
        'перевод из',
        'vneseno',
        'перевел\(а\) вам',
        'postupil perevod',
        'перевод денежных средств',
        'перевод на карту',
        'zachislenie',
    ];

    $exceptions = [
        '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\.\sтеперь\sна\sкарте\s.+₽$',
        '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s-\sбаланс\:\s.+$',
        '^\d{2}\.\d{2}\.\d{2}\s\d{2}\:\d{2}\sзачисление\s\*(?<card_last_digits>\d{4})\srur\s(?<amount>\d+(.\d+){0,3})\;\sостаток\s.+$',
        '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\sот\s.+теперь\sна\sсчете\s.+₽$',
        '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sтеперь\sу\sвас\:\s.+$',
        '^\d{2}\:\d{2}\sперевод\s(?<amount>\d+(.\d+){0,3})р\sна\sкарту\s.+\sбаланс\s.+$',
        '^\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sбаланс\:\s.+$',
        '^совкомбанк\s\+\s(?<amount>\d+(.\d+){0,3})\s₽\s—\sбаланс\:\s.+(?<card_last_digits>\d{4})$'
    ];

    $message = normalizeMessage($message);

    $amount = null;

    foreach ($exceptions as $exception) {
        $regex = '/' . $exception . '/mi';
        preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

        if (! empty($matches[0]['amount'])) {
            $amount = $matches[0]['amount'];
            break;
        }
    }

    if (empty($amount)) {
        foreach ($triggerPatterns as $triggerWord) {
            $triggerWord = mb_strtolower($triggerWord);

            $regex = '/' . $triggerWord . '/mi';
            preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

            if (! empty($matches[0])) {
                $amount = findAmount($message);
                break;
            }
        }
    }

    return $amount;
}

function findAmount($message): ?string
{
    $amountRegex = '(\s|\+)(?<amount>\d+(.\d+){0,3})\s{0,1}(RUB|rub|р|p|₽|RUR|rur|rurcard2card|руб)(\s|\.|\,|\;)';

    $regex = '/' . $amountRegex . '/mi';
    preg_match_all($regex, $message, $matches, PREG_SET_ORDER);

    $amount = null;
    if (! empty($matches[0]['amount'])) {
        $amount = $matches[0]['amount'];
    }

    return $amount;
}

function normalizeMessage(string $message): string
{
    $message = str_replace("\u{A0}", ' ', $message);
    $message = str_replace("\r\n", ' ', $message);
    $message = str_replace("\r", ' ', $message);
    $message = str_replace("\n", ' ', $message);
    $message = trim($message);
    return mb_strtolower($message);
}

require __DIR__.'/auth.php';
