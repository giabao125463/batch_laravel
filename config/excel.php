<?php

return [
    'exports' => [
        'csv' => [
            'delimiter'              => ',',
            'enclosure'              => '"',
            'line_ending'            => "\n",
            'use_bom'                => false,
            'include_separator_line' => false,
            'excel_compatibility'    => false,
            'item'                   => [
                'max_length' => 40,
            ],
        ],
    ],
    'imports' => [
        'csv'         => [
            'chunk_size'             => env('CSV_IMPORT_CHUNK_SIZE', 1000),
            'delimiter'              => "\t",
            'enclosure'              => '"',
            'escape_character'       => '\\',
            'contiguous'             => false,
            'input_encoding'         => env('CSV_FILE_INPUT_ENCODING', 'sjis'),
        ],
    ],
];
