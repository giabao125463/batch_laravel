<?php

return [
    'api' => [
        'url' => env('MS_API_HOST', 'https://www.makeshop.jp'),
        'default_sleep' => 1000000,
        'shopid' => env('MS_API_SHOP_ID', 'RadicalShop'),
        'shop_pass' => env('MS_API_SHOP_PASS', 'rdclopti2014'),
        'product' => [
            'paths' => [
                'auth' => '/api/product/auth/',
                'key' => '/api/webftp/index.html',
                'search' => '/api/product/search/',
            ],
            'token' => env('MS_API_TOKEN_PRODUCT', '7704ff0cccfdac12d2c407b3094cca51'),
            'file_names' => [
                'update' => env('MS_API_PRODUCT_UPDATE_FILE_TARGET', 'updateProduct.csv'),
            ]
        ],
        'order' => [
            'paths' => [
                'info' => '/api/orderinfo/index.html',
            ],
            'default_status' => 0,
            'token' => env('MS_API_TOKEN_ORDER', 'e1ad547162b9a94654fe7885f0a82572'),
        ],
        'customer' => [
            'token' => env('MS_API_TOKEN_CUSTOMER', '2647cc5e36cc4637f7ccabd1e9155bdd'),
        ],
        'response' => [
            'zero_date_format' => '0000-00-00',
        ],
    ],
    'batch' => [
        'downtime_period' => 5,
        'loop_limit'      => 100000,
        'order'           => [
            'limit_per_batch' => 100,
        ],
        'product' => [
            'limit_per_page' => 50,
        ]
    ],
    'max_potsuban' => 9999,
];
