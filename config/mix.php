<?php

return [
    'csv' => [
        'kanareplace' => env('MIX_CSV_KANA_REPLACE', '・'),
    ],
    'order' => [
        'mixnum' => [
            'prefix' => env('MIX_ORDER_MIXNUM_PREFIX', 7),
            'length' => env('MIX_ORDER_MIXNUM_LENGTH', 7),
        ],
    ],
    'sftp' => [
        'paths'  => [
            'input'  => env('MIX_SFTP_PATH_INPUT_DIR', '/EC2MIX/uriage/'),
            'uriage' => env('MIX_SFTP_PATH_INPUT_DIR_URIAGE', '/EC2MIX/uriage/'),
        ],
        'uriage_csv' => [
            'class'             => env('MIX_SFTP_URIAGE_CSV_CLASS', 'H'),
            'name_prefix'       => env('MIX_SFTP_URIAGE_NAME_PREFIX', 'MAO'),
            'shipping_days_ago' => env('MIX_SFTP_URIAGE_CSV_SHIPPING_DAYS_AGO', '30'),
        ],
    ],
];