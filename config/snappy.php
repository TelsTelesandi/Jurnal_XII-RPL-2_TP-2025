<?php

return [
    'pdf' => [
        'enabled' => true,
        'binary' => 'D:\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
            'no-outline' => true,
            'encoding' => 'UTF-8',
            'margin-top' => 15,
            'margin-bottom' => 15,
            'page-size' => 'A4',
            'load-error-handling' => 'ignore',
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        'binary' => 'D:\\wkhtmltopdf\\bin\\wkhtmltoimage.exe',
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
];
