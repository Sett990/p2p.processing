<?php

return [
    'currencies' => [
        'RUB' => [
            'precision'           => 2,
            'subunit'             => 100,
            'base'                => false,
            'symbol'              => '₽',
            'name'                => 'Российский рубль',
        ],
        'KZT' => [
            'precision'           => 2,
            'subunit'             => 100,
            'base'                => false,
            'symbol'              => '₸',
            'name'                => 'Казахстанский тенге',
        ],
        'UZS' => [
            'precision'           => 2,
            'subunit'             => 100,
            'base'                => false,
            'symbol'              => 'Soʻm',
            'name'                => 'Узбекский сум',
        ],
        'BYN' => [
            'precision'           => 2,
            'subunit'             => 100,
            'base'                => false,
            'symbol'              => 'Br',
            'name'                => 'Белорусский рубль',
        ],
        //base
        'USDT' => [
            'precision'           => 2,
            'subunit'             => 100,
            'base'                => true,
            'symbol'              => 'USDT',
            'name'                => 'Tether (USDT)',
        ],
    ]
];
