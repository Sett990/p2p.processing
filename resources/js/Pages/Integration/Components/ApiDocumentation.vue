<script setup>
import {ref} from 'vue';

const formatJSON = (obj) => {
    return JSON.stringify(obj, null, 2);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Навигация по документации -->
        <div class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-xl mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Содержание
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                    <a href="#about" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Введение
                    </a>
                    <a href="#base" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Основы
                    </a>
                    <a href="#base-methods" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Базовые методы
                    </a>
                    <a href="#about-api" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Описание API
                    </a>
                    <a href="#merchant-api" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Мерчант API
                    </a>
                    <a href="#h2h-api" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        H2H API
                    </a>
                    <a href="#auto-withdrawals" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Авто вывод с баланса
                    </a>
                    <a href="#order-statuses" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Описание статусов сделок
                    </a>
                    <a href="#addition" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Общее дополнение к описанию API
                    </a>
                    <a href="#callback" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Callbacks
                    </a>
                    <a href="#payouts" class="btn btn-outline btn-sm justify-start normal-case hover:btn-primary transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        API для выплат
                    </a>
                </div>
            </div>
        </div>

        <!-- Введение -->
        <div id="about" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Введение</h2>
                <p class="text-base-content/80">
                    Ниже представлено описание того как работает API, с помощью которого вы сможете сделать интеграцию с вашим проектом.
                </p>
            </div>
        </div>

        <!-- Основы -->
        <div id="base" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Основы</h2>
                <p class="mb-4 text-base-content/80">
                    Все запросы к API должны содержать обязательные заголовки:
                </p>
                <ul class="list-disc list-inside space-y-2 mb-4 text-base-content/80 ml-2">
                    <li><strong>Accept: application/json</strong> - нужно чтобы сервер знал в каком формате вернуть ответ.</li>
                    <li><strong>Access-Token: token</strong> - нужно для авторизации. Находится в админке, раздел "Интеграция".</li>
                </ul>

                <h3 class="text-xl font-semibold mt-6 mb-3">Ответы сервера</h3>

                <div class="space-y-4">
                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" checked />
                        <div class="collapse-title text-lg font-medium">
                            Успех HTTP 200
                        </div>
                        <div class="collapse-content">
                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [] }) }}</code></pre>
                        </div>
                    </div>

                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-medium">
                            Ошибка валидации запроса HTTP 422
                        </div>
                        <div class="collapse-content">
                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ message: "Общее описание ошибки", errors: { "название параметра": ["Описание ошибки поля"] } }) }}</code></pre>
                        </div>
                    </div>

                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-medium">
                            Ошибки в бизнес логике HTTP 400
                        </div>
                        <div class="collapse-content">
                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: false, message: "Описание ошибки" }) }}</code></pre>
                        </div>
                    </div>

                    <div class="collapse collapse-arrow bg-base-200">
                        <input type="checkbox" />
                        <div class="collapse-title text-lg font-medium">
                            Ошибка сервера HTTP 500
                        </div>
                        <div class="collapse-content">
                            <pre class="bg-base-300 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ message: "Internal Server Error" }) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Базовые методы -->
        <div id="base-methods" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Базовые методы</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Доступные валюты</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/currencies</code>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [{ currency: "rub", precision: 2, symbol: "₽", name: "Российский рубль" }] }) }}</code></pre>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Доступные платежные методы</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/payment-gateways</code>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: [{ name: "Сбербанк", code: "sberbank", schema: "100000000111", currency: "rub", min_limit: "1000", max_limit: "100000", reservation_time: 10, detail_types: ["card", "phone", "account_number"] }] }) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Описание API -->
        <div id="about-api" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Описание API</h2>
                <ul class="list-disc list-inside space-y-2 text-base-content/80 ml-2">
                    <li>В системе есть два вида API - <strong>Merchant API</strong> и <strong>H2H API</strong></li>
                    <li><strong>Merchant API</strong> - упрощенная версия api для более простой интеграции.</li>
                    <li><strong>H2H API</strong> - версия api дающая полную возможность управления сделками, спорами. Выдает больше информации.</li>
                    <li>Важное замечание - для сделок созданных через <strong>H2H API</strong> не предоставляется платежная ссылка.</li>
                </ul>
            </div>
        </div>

        <!-- Мерчант API -->
        <div id="merchant-api" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Мерчант API</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Создать сделку</h3>
                        <div class="badge badge-secondary badge-lg mb-3">POST</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/merchant/order</code>

                        <div class="mt-4 space-y-4">
                            <div>
                                <h4 class="font-semibold mb-2">Заголовки</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-base-content/80 ml-2">
                                    <li><strong>X-Max-Wait-Ms: 30000</strong> - (не обязательный) Этот заголовок указывает в миллисекундах, как долго вы готовы ждать выдачу сделки. 1000 = 1сек. Минимальный срок — это одна секунда. Если вы укажете этот параметр, то при высокой нагрузке когда система за указанное время не успеет выдать сделку, то она вернет ошибку, что не успела обработать запрос HTTP 504. Таким образом, вам не нужно будет ждать долгий зависший запрос, не нужно будет обрывать запрос, и мы не будем создавать псевдо-сделку, которую вы никогда не обработаете.</li>
                                    <li>По умолчанию система ждет полминуты прежде чем вернуть ошибку ниже.</li>
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
                                                <td>сумма сделки. (целое число)</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_gateway</code></td>
                                                <td>код платежного метода. Не обязательно если указан currency.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">currency</code></td>
                                                <td>код валюты. Не обязательно если указан payment_gateway.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_detail_type</code></td>
                                                <td>тип реквизита с которым будет создана сделка card, phone, account_number.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">merchant_id</code> <span class="text-error">*</span></td>
                                                <td>uuid мерчанта. Можно найти на странице мерчанта в разделе настройки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">callback_url</code></td>
                                                <td>POST ссылка на которую будет направлена информация об изменении статуса сделки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">success_url</code></td>
                                                <td>GET ссылка на которую перенаправлен пользователь в случае успешной оплаты.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">fail_url</code></td>
                                                <td>GET ссылка на которую будет перенаправлен пользователь в случае если оплата не была произведена.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">manually</code></td>
                                                <td>если передано со значением "1", то клиенту будет дана возможность самому выбрать платежный метод.</td>
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
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить сделку</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/merchant/order/{order_id}</code>
                        <p class="mt-2 text-sm text-base-content/80">Содержит тоже самое, что объект при создании сделки.</p>
                        <p class="mt-1 text-sm text-base-content/80">Альтернатива: <code class="bg-base-200 px-1 rounded">/api/merchant/order/{merchant_id}/{external_id}</code></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- H2H API -->
        <div id="h2h-api" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">H2H API</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Создать сделку</h3>
                        <div class="badge badge-secondary badge-lg mb-3">POST</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order</code>

                        <div class="mt-4 space-y-4">
                            <div>
                                <h4 class="font-semibold mb-2">Заголовки</h4>
                                <ul class="list-disc list-inside space-y-1 text-sm text-base-content/80 ml-2">
                                    <li><strong>X-Max-Wait-Ms: 30000</strong> - (не обязательный) Этот заголовок указывает в миллисекундах, как долго вы готовы ждать выдачу сделки. 1000 = 1сек. Минимальный срок — это одна секунда. Если вы укажете этот параметр, то при высокой нагрузке когда система за указанное время не успеет выдать сделку, то она вернет ошибку, что не успела обработать запрос HTTP 504. Таким образом, вам не нужно будет ждать долгий зависший запрос, не нужно будет обрывать запрос, и мы не будем создавать псевдо-сделку, которую вы никогда не обработаете.</li>
                                    <li>По умолчанию система ждет полминуты прежде чем вернуть ошибку ниже.</li>
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
                                                <td>сумма сделки. (целое число)</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_gateway</code></td>
                                                <td>код платежного метода. Не обязательно если указан currency.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">currency</code></td>
                                                <td>код валюты. Не обязательно если указан payment_gateway.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">payment_detail_type</code></td>
                                                <td>тип реквизита с которым будет создана сделка card, phone, account_number.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">merchant_id</code> <span class="text-error">*</span></td>
                                                <td>uuid мерчанта. Можно найти на странице мерчанта в разделе настройки.</td>
                                            </tr>
                                            <tr>
                                                <td><code class="bg-base-200 px-1 rounded">callback_url</code></td>
                                                <td>POST ссылка на которую будет направлена информация об изменении статуса сделки.</td>
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
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить сделку</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}</code>
                        <p class="mt-2 text-sm text-base-content/80">Возвращает такой же объект как при создании сделки.</p>
                        <p class="mt-1 text-sm text-base-content/80">Альтернатива: <code class="bg-base-200 px-1 rounded">/api/h2h/order/{merchant_id}/{external_id}</code></p>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Закрыть сделку</h3>
                        <div class="badge badge-warning badge-lg mb-3">PATCH</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/cancel</code>
                        <p class="mt-2 text-sm text-base-content/80">Досрочно закрывает сделку если она находится в статусе pending и не имеет открытых споров.</p>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Открыть спор</h3>
                        <div class="badge badge-secondary badge-lg mb-3">POST</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/dispute</code>
                        <p class="mt-2 text-sm text-base-content/80">Если сделка все еще открыта, то она будет закрыта перед открытием спора, так если бы вы вызвали предыдущий метод выше.</p>

                        <div class="mt-4">
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
                                            <td><code class="bg-base-200 px-1 rounded">receipt</code> <span class="text-error">*</span></td>
                                            <td>изображение jpeg,jpg,png,pdf преобразованное в base64. Размер файла до 5МБ.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { order_id: "3db07a16...", status: "pending", cancel_reason: null } }) }}</code></pre>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить спор</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/h2h/order/{order_id}/dispute</code>
                        <p class="mt-2 text-sm text-base-content/80">Ответ такой же как при открытии спора.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Авто вывод с баланса -->
        <div id="auto-withdrawals" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Авто вывод с баланса</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить доступный баланс</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/wallet/balance</code>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { balance: "10000.00" } }) }}</code></pre>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Создать запрос на вывод</h3>
                        <div class="badge badge-secondary badge-lg mb-3">POST</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/wallet/withdraw</code>

                        <div class="mt-4">
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
                                            <td><code class="bg-base-200 px-1 rounded">amount</code> <span class="text-error">*</span></td>
                                            <td>сумма вывода (целое число)</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">address</code> <span class="text-error">*</span></td>
                                            <td>адрес куда сделать вывод средств.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">network</code> <span class="text-error">*</span></td>
                                            <td>USDT сеть bsc, arb, trx</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { invoice_id: "...", tx_hash: "..." } }) }}</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Описание статусов сделок -->
        <div id="order-statuses" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Описание статусов сделок</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Status</h3>
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
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-3">Sub Status</h3>
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
                                        <td>Ждет выбора реквизитов.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">waiting_for_payment</code></td>
                                        <td>Ждет платежа.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">waiting_for_dispute_to_be_resolved</code></td>
                                        <td>Ждет решения спора.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">canceled_by_dispute</code></td>
                                        <td>Отменен в результате спора.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">expired</code></td>
                                        <td>Отменен по истечению времени.</td>
                                    </tr>
                                    <tr>
                                        <td><code class="bg-base-200 px-1 rounded">cancelled</code></td>
                                        <td>Отменен вручную.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Общее дополнение к описанию API -->
        <div id="addition" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Общее дополнение к описанию API</h2>
                <ul class="list-disc list-inside space-y-2 text-base-content/80 ml-2">
                    <li>Не для всех вариантов параметров может быть доступный реквизит. Поэтому сервер вернет сообщение, что реквизит не найден.</li>
                    <li>Параметр payment_gateway - создаст сделку только для этого платежного метода. В то время как параметр currency создаст сделку в рамках это валюты, но для любого платежного метода.</li>
                    <li>Параметры payment_gateway и currency взаимоисключающие и не могут быть использованы одновременно.</li>
                    <li>Если сумма сделки выходит за лимиты указанного платежного метода или всех доступных методов указанных в <strong>/api/payment-gateways</strong> то сервер вернет ошибку что подходящий платежный метод не найден.</li>
                    <li>Параметр callback_url также есть в настройках мерчанта, но если параметр передать в запросе к API, то он будет старше. Т.е. уведомление будет отправлено по url указанного в запросе, но не в админке.</li>
                    <li>Параметр payment_detail_type нужно использовать аккуратно. И чаще всего в связке с параметров currency. Так как не для всех платежных методов если реквизиты всех типов.</li>
                    <li>Cумма сделки может изменить после ее создания. Вернется в параметре amount. Так как существует наценка в виде комиссии клиента. Указывается в настройках мерчанта. Например при комиссии клиента в 4%, сумма 1000 будет изменена на 1040.</li>
                    <li>Сделкой можно управлять только через соответствующий API</li>
                </ul>
            </div>
        </div>

        <!-- Уведомление об изменении статуса платежа -->
        <div id="callback" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">Уведомление об изменении статуса платежа</h2>
                <ul class="list-disc list-inside space-y-2 text-base-content/80 ml-2">
                    <li>По ссылке указанной в настройках мерчанта, или переданной в параметре callback_url при создании сделки, будет отправлено уведомление (POST запрос) если сделка изменит свой статус.</li>
                    <li>Уведомление содержит данные соответствующие данным которые возвращает метод <strong>GET /api/h2h/order/{order_id}</strong> или <strong>GET /api/merchant/order/{order_id}</strong> в зависимости от используемого API. Ниже пример для H2H API.</li>
                </ul>
            </div>
        </div>

        <!-- API для выплат -->
        <div id="payouts" class="card bg-base-100 shadow-lg">
            <div class="card-body">
                <h2 class="card-title text-2xl mb-4">API для выплат</h2>
                <p class="mb-4 text-base-content/80">Доступ нужно запросить у администратора.</p>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить список предложений на выплату</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/payout/offers</code>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { rub: { sberbank_rub: { max_amount: 100000, min_amount: 1000, currency: "rub", detail_type: "card", payment_gateway: { name: "Сбербанк", name_with_currency: "Сбербанк RUB", code: "sberbank_rub" }, offers_count: 1, recommended_max_amount: 100000, recommended_min_amount: 1000 } } } }) }}</code></pre>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Создать выплату</h3>
                        <div class="badge badge-secondary badge-lg mb-3">POST</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/payout</code>

                        <div class="mt-4">
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
                                            <td><code class="bg-base-200 px-1 rounded">payout_gateway_id</code></td>
                                            <td>id направления, находится в админке.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">external_id</code></td>
                                            <td>id сделки на стороне внешнего сервиса. Должен быть уникальным для направления.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">detail</code></td>
                                            <td>реквизиты на которые будут отправлены средства.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">detail_type</code></td>
                                            <td>тип реквизитов.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">detail_initials</code></td>
                                            <td>держатель реквизитов.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">amount</code></td>
                                            <td>сумма выплаты (в валюте платежного метода).</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">payment_gateway</code></td>
                                            <td>платежный метод на который оформлен реквизит.</td>
                                        </tr>
                                        <tr>
                                            <td><code class="bg-base-200 px-1 rounded">callback_url</code></td>
                                            <td>ссылка на которую будет направлена информация об изменении статуса выплаты. Не обязательный параметр.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h4 class="font-semibold mb-2">Ответ сервера</h4>
                            <pre class="bg-base-200 p-4 rounded-lg overflow-x-auto text-sm"><code>{{ formatJSON({ success: true, data: { uuid: "...", external_id: "...", detail: "1000200030004000", detail_type: "card", detail_initials: "Петр К.", payout_amount: "1000", currency: "rub", base_liquidity_amount: "9.31", liquidity_amount: "10.14", liquidity_currency: "usdt", service_commission_rate: 9, service_commission_amount: "0.83", trader_profit_amount: "9.31", trader_exchange_markup_rate: 2.5, trader_exchange_markup_amount: "0.23", base_exchange_price: "110.07", exchange_price: "107.32", status: "pending", sub_status: "processing_by_trader", callback_url: "https://example.com/callback", payment_gateway: "sberbank_rub", payment_gateway_name: "Сбербанк", finished_at: null, expires_at: 1736145380, created_at: 1736144380 } }) }}</code></pre>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div>
                        <h3 class="text-xl font-semibold mb-2">Получить выплату</h3>
                        <div class="badge badge-primary badge-lg mb-3">GET</div>
                        <code class="bg-base-200 px-2 py-1 rounded text-sm">/api/payout/{uuid}</code>
                        <p class="mt-2 text-sm text-base-content/80">Ответ такой же, как при создании выплаты.</p>
                    </div>
                </div>
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


html {
    scroll-behavior: smooth;
}

[id] {
    scroll-margin-top: 1rem;
}
</style>

