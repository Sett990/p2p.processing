<script setup>
const formatJSON = (obj) => JSON.stringify(obj, null, 2);

const tocSections = [
    {id: 'about', title: 'Введение'},
    {id: 'base', title: 'Основы'},
    {id: 'order-statuses', title: 'Статусы сделок'},
    {id: 'callback', title: 'Callbacks'},
    {id: 'base-methods', title: 'Базовые методы'},
    {id: 'merchant-api', title: 'H2Form API'},
    {id: 'h2h-api', title: 'H2Host API'},
    {id: 'auto-withdrawals', title: 'Авто вывод'},
];
</script>

<template>
    <div class="space-y-10">
        <div class="flex gap-6">
            <aside>
                <div class="card menu menu-sm p-0 bg-base-100 shadow sticky top-6">
                    <div class="card-body">
                        <h3 class="card-title text-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Содержание
                        </h3>
                        <ul class="w-full">
                            <li v-for="section in tocSections" :key="section.id">
                                <a :href="`#${section.id}`" class="truncate">
                                    {{ section.title }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </aside>

            <div class="space-y-6">
                <article id="about" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Введение</h2>
                        <p class="text-base-content/80">
                            Ниже представлено описание того как работает API, с помощью которого вы сможете сделать интеграцию с вашим проектом.
                        </p>
                    </div>
                </article>

                <article id="base" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Основы работы API</h2>

                        <section class="space-y-4">
                            <div>
                                <p class="text-base-content/80">
                                    Все запросы к API должны содержать обязательные заголовки:
                                </p>
                                <ul class="list-disc list-inside space-y-2 mt-3 text-base-content/80 ml-2">
                                    <li><strong>Accept: application/json</strong> — формат ответа.</li>
                                    <li><strong>Access-Token: token</strong> — ключ авторизации из раздела «Интеграция».</li>
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-xl font-semibold mb-3">Ответы сервера</h3>
                                <div class="join join-vertical w-full space-y-2">
                                    <div class="collapse collapse-arrow bg-base-200 join-item">
                                        <input type="checkbox" checked />
                                        <div class="collapse-title text-lg font-medium">Успех HTTP 200</div>
                                        <div class="collapse-content">
                                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [] }) }}</code></pre>
                                        </div>
                                    </div>
                                    <div class="collapse collapse-arrow bg-base-200 join-item">
                                        <input type="checkbox" />
                                        <div class="collapse-title text-lg font-medium">Ошибка валидации HTTP 422</div>
                                        <div class="collapse-content">
                                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ message: "Общее описание ошибки", errors: { "название параметра": ["Описание ошибки поля"] } }) }}</code></pre>
                                        </div>
                                    </div>
                                    <div class="collapse collapse-arrow bg-base-200 join-item">
                                        <input type="checkbox" />
                                        <div class="collapse-title text-lg font-medium">Ошибки бизнес-логики HTTP 400</div>
                                        <div class="collapse-content">
                                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: false, message: "Описание ошибки" }) }}</code></pre>
                                        </div>
                                    </div>
                                    <div class="collapse collapse-arrow bg-base-200 join-item">
                                        <input type="checkbox" />
                                        <div class="collapse-title text-lg font-medium">Ошибка сервера HTTP 500</div>
                                        <div class="collapse-content">
                                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ message: "Internal Server Error" }) }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </article>

                <article id="order-statuses" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Описание статусов сделок</h2>

                        <section class="space-y-3">
                            <h3 class="text-xl font-semibold">Status</h3>
                            <div class="overflow-x-auto">
                                <table class="table table-zebra w-full">
                                    <thead>
                                    <tr>
                                        <th>Значение</th>
                                        <th>Описание</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">success</code></td>
                                        <td>Операция успешно завершена.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">pending</code></td>
                                        <td>Операция находится в ожидании обработки.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">fail</code></td>
                                        <td>Операция завершилась неудачно.</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section class="space-y-3">
                            <h3 class="text-xl font-semibold">Sub Status</h3>
                            <div class="overflow-x-auto">
                                <table class="table table-zebra w-full">
                                    <thead>
                                    <tr>
                                        <th>Значение</th>
                                        <th>Описание</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">accepted</code></td>
                                        <td>Закрыт вручную.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">successfully_paid</code></td>
                                        <td>Закрыт автоматически.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">successfully_paid_by_resolved_dispute</code></td>
                                        <td>Закрыт в результате принятого спора.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">waiting_details_to_be_selected</code></td>
                                        <td>Ждёт выбора реквизитов.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">waiting_for_payment</code></td>
                                        <td>Ждёт платежа.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">waiting_for_dispute_to_be_resolved</code></td>
                                        <td>Ждёт решения спора.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">canceled_by_dispute</code></td>
                                        <td>Отменён в результате спора.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">expired</code></td>
                                        <td>Отменён по истечению времени.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">cancelled</code></td>
                                        <td>Отменён вручную.</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </article>

                <article id="callback" class="card bg-base-100 shadow">
                    <div class="card-body space-y-3">
                        <h2 class="card-title text-2xl">Уведомление об изменении статуса платежа</h2>
                        <ul class="list-disc list-inside space-y-2 text-base-content/80 ml-2">
                            <li>По ссылке из настроек мерчанта или указанной в <code>callback_url</code> при создании сделки отправляется POST-уведомление, когда статус сделки меняется.</li>
                            <li>Тело уведомления соответствует ответу <strong>GET /api/h2h/order/{order_id}</strong> или <strong>GET /api/merchant/order/{order_id}</strong> — в зависимости от API.</li>
                        </ul>
                    </div>
                </article>


                <article id="base-methods" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Базовые методы</h2>

                        <div class="grid gap-6">
                            <section class="p-4 rounded-xl border border-base-200 space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Доступные валюты</h3>
                                    <span class="badge badge-primary badge-lg">GET</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/currencies</code>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [{ currency: "rub", precision: 2, symbol: "₽", name: "Российский рубль" }] }) }}</code></pre>
                                </div>
                            </section>

                            <section class="p-4 rounded-xl border border-base-200 space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Доступные платежные методы</h3>
                                    <span class="badge badge-primary badge-lg">GET</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/payment-gateways</code>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [{ name: "Сбербанк", code: "sberbank", schema: "100000000111", currency: "rub", min_limit: "1000", max_limit: "100000", reservation_time: 10, detail_types: ["card", "phone", "account_number"] }] }) }}</code></pre>
                                </div>
                            </section>
                        </div>
                    </div>
                </article>

                <article id="merchant-api" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Host To Form API</h2>

                        <section class="space-y-4">
                            <div class="rounded-xl border border-base-200 p-4 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Создать сделку</h3>
                                    <span class="badge badge-secondary badge-lg">POST</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/merchant/order</code>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Заголовки</h4>
                                    <ul class="list-disc list-inside space-y-1 text-sm text-base-content/80 ml-2">
                                        <li><strong>X-Max-Wait-Ms: 30000</strong> — необязательно. Указывает, сколько ждать выдачи сделки (минимум 1 секунда). При превышении вернётся HTTP 504 без «зависших» запросов.</li>
                                        <li>По умолчанию система ждёт полминуты, затем возвращает ошибку ниже.</li>
                                    </ul>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm mt-2"><code>{{ formatJSON({ success: false, message: "Не удалось обработать запрос вовремя. Повторите попытку позже." }) }}</code></pre>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Параметры запроса</h4>
                                    <div class="overflow-x-auto">
                                        <table class="table table-zebra w-full">
                                            <thead>
                                            <tr>
                                                <th>Параметр</th>
                                                <th>Описание</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">external_id</code> <span class="text-error">*</span></td>
                                                <td>id сделки на стороне внешнего сервиса. Должен быть уникальным для мерчанта.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">amount</code> <span class="text-error">*</span></td>
                                                <td>сумма сделки (целое число).</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_gateway</code></td>
                                                <td>код платежного метода. Не обязательно, если указан currency.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">currency</code></td>
                                                <td>код валюты. Не обязателен, если указан payment_gateway.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_detail_type</code></td>
                                                <td>тип реквизита: card, phone, account_number.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">merchant_id</code> <span class="text-error">*</span></td>
                                                <td>uuid мерчанта. Можно найти на странице мерчанта в разделе настройки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">callback_url</code></td>
                                                <td>POST-ссылка, куда придёт статус сделки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">success_url</code></td>
                                                <td>GET-ссылка для успешной оплаты.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">fail_url</code></td>
                                                <td>GET-ссылка для неуспешной оплаты.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">manually</code></td>
                                                <td>значение "1" позволяет клиенту выбрать платёжный метод.</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { order_id: "4b3a163b...", external_id: "...", merchant_id: "...", amount: "1000", currency: "rub", status: "pending", sub_status: "pending", callback_url: null, success_url: null, fail_url: null, payment_gateway: "sberbank", payment_gateway_schema: "100000000111", payment_gateway_name: "Сбербанк", finished_at: null, expires_at: 1731375451, created_at: 1731375391, payment_link: "https://example.com/payment/4b3a163b..." } }) }}</code></pre>
                                </div>
                            </div>
                        </section>

                        <section class="border border-base-200 rounded-xl p-4 space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-semibold">Получить сделку</h3>
                                <span class="badge badge-primary badge-lg">GET</span>
                                <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/merchant/order/{order_id}</code>
                            </div>
                            <p class="text-sm text-base-content/80">Возвращает те же поля, что и ответ при создании.</p>
                            <p class="text-sm text-base-content/80">Альтернатива: <code class="bg-base-200 px-1 rounded">/api/merchant/order/{merchant_id}/{external_id}</code></p>
                        </section>
                    </div>
                </article>

                <article id="h2h-api" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Host To Host API</h2>

                        <section class="space-y-4">
                            <div class="rounded-xl border border-base-200 p-4 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Создать сделку</h3>
                                    <span class="badge badge-secondary badge-lg">POST</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order</code>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Заголовки</h4>
                                    <ul class="list-disc list-inside space-y-1 text-sm text-base-content/80 ml-2">
                                        <li><strong>X-Max-Wait-Ms: 30000</strong> — необязательный таймаут ожидания выдачи сделки.</li>
                                        <li>По умолчанию система ждёт полминуты прежде чем вернуть ошибку ниже.</li>
                                    </ul>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm mt-2"><code>{{ formatJSON({ success: false, message: "Не удалось обработать запрос вовремя. Повторите попытку позже." }) }}</code></pre>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Описание параметров запроса</h4>
                                    <div class="overflow-x-auto">
                                        <table class="table table-zebra w-full">
                                            <thead>
                                            <tr>
                                                <th>Параметр</th>
                                                <th>Описание</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">external_id</code> <span class="text-error">*</span></td>
                                                <td>id сделки на стороне внешнего сервиса. Должен быть уникальным для мерчанта.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">amount</code> <span class="text-error">*</span></td>
                                                <td>сумма сделки (целое число).</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_gateway</code></td>
                                                <td>код платежного метода. Не обязательно, если указан currency.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">currency</code></td>
                                                <td>код валюты. Не обязателен, если указан payment_gateway.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_detail_type</code></td>
                                                <td>тип реквизита: card, phone, account_number.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">merchant_id</code> <span class="text-error">*</span></td>
                                                <td>uuid мерчанта. Можно найти на странице мерчанта в разделе настройки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">callback_url</code></td>
                                                <td>POST-ссылка, на которую придёт статус.</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { order_id: "3db07a16...", external_id: "...", merchant_id: "3db07a16...", base_amount: "1000", amount: "1040", profit: "9.94", merchant_profit: "9.05", currency: "rub", profit_currency: "usdt", conversion_price_currency: "rub", conversion_price: "100.77", status: "pending", sub_status: "pending", callback_url: "...", payment_gateway: "sberbank", payment_gateway_schema: "100000000111", payment_gateway_name: "Сбербанк", payment_detail: { detail: "1000200030004000", detail_type: "card", initials: "Пол Атрейдес" }, merchant: { name: "...", description: "..." }, finished_at: null, expires_at: 1731375451, created_at: 1731375391, current_server_time: 1731655862 } }) }}</code></pre>
                                </div>
                            </div>
                        </section>

                        <section class="grid gap-4">
                            <div class="border border-base-200 rounded-xl p-4 space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Получить сделку</h3>
                                    <span class="badge badge-primary badge-lg">GET</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}</code>
                                </div>
                                <p class="text-sm text-base-content/80">Возвращает такой же объект, как при создании.</p>
                                <p class="text-sm text-base-content/80">Альтернатива: <code class="bg-base-200 px-1 rounded">/api/h2h/order/{merchant_id}/{external_id}</code></p>
                            </div>

                            <div class="border border-base-200 rounded-xl p-4 space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Закрыть сделку</h3>
                                    <span class="badge badge-warning badge-lg text-white">PATCH</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/cancel</code>
                                </div>
                                <p class="text-sm text-base-content/80">Досрочно закрывает сделку, если она в статусе pending и без открытых споров.</p>
                            </div>

                            <div class="border border-base-200 rounded-xl p-4 space-y-4">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Открыть спор</h3>
                                    <span class="badge badge-secondary badge-lg">POST</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/dispute</code>
                                </div>
                                <p class="text-sm text-base-content/80">Если сделка ещё открыта, она будет закрыта перед созданием спора.</p>

                                <div>
                                    <h4 class="font-semibold mb-2">Параметры</h4>
                                    <div class="overflow-x-auto">
                                        <table class="table table-zebra w-full">
                                            <thead>
                                                <tr>
                                                    <th>Параметр</th>
                                                    <th>Описание</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code class="bg-base-200 px-1 rounded">receipt</code> <span class="text-error">*</span></td>
                                                    <td>изображение jpeg,jpg,png,pdf в base64 до 5 МБ.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { order_id: "3db07a16...", status: "pending", cancel_reason: null } }) }}</code></pre>
                                </div>
                            </div>

                            <div class="border border-base-200 rounded-xl p-4 space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Получить спор</h3>
                                    <span class="badge badge-primary badge-lg">GET</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/dispute</code>
                                </div>
                                <p class="text-sm text-base-content/80">Ответ такой же, как при открытии спора.</p>
                            </div>
                        </section>
                    </div>
                </article>

                <article id="auto-withdrawals" class="card bg-base-100 shadow">
                    <div class="card-body space-y-4">
                        <h2 class="card-title text-2xl">Авто вывод с баланса</h2>

                        <div class="grid lg:grid-cols-1 gap-6">
                            <section class="border border-base-200 rounded-xl p-4 space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Получить доступный баланс</h3>
                                    <span class="badge badge-primary badge-lg">GET</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/wallet/balance</code>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { balance: "10000.00" } }) }}</code></pre>
                                </div>
                            </section>

                            <section class="border border-base-200 rounded-xl p-4 space-y-3">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h3 class="text-xl font-semibold">Создать запрос на вывод</h3>
                                    <span class="badge badge-secondary badge-lg">POST</span>
                                    <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/wallet/withdraw</code>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Параметры запроса</h4>
                                    <div class="overflow-x-auto">
                                        <table class="table table-zebra w-full">
                                            <thead>
                                                <tr>
                                                    <th>Параметр</th>
                                                    <th>Описание</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><code class="bg-base-200 px-1 rounded">amount</code> <span class="text-error">*</span></td>
                                                    <td>сумма вывода (целое число).</td>
                                                </tr>
                                                <tr>
                                                    <td><code class="bg-base-200 px-1 rounded">address</code> <span class="text-error">*</span></td>
                                                    <td>адрес, куда отправить средства.</td>
                                                </tr>
                                                <tr>
                                                    <td><code class="bg-base-200 px-1 rounded">network</code> <span class="text-error">*</span></td>
                                                    <td>USDT сеть: bsc, arb или trx.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="font-semibold mb-2">Ответ сервера</h4>
                                    <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { invoice_id: "...", tx_hash: "..." } }) }}</code></pre>
                                </div>
                            </section>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</template>

<style scoped>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}

code {
    font-family: 'Courier New', monospace;
}

:global(html) {
    scroll-behavior: smooth;
}

:global([id]) {
    scroll-margin-top: 1rem;
}
</style>
