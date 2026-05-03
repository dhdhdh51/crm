<?php
return [
    'name'     => 'VastuVeda Realty CRM',
    'version'  => '1.0.0',
    'debug'    => true,        // TEMP: set false once login is confirmed working
    'timezone' => 'Asia/Kolkata',
    'locale'   => 'en_IN',
    'currency' => '₹',
    'per_page' => 15,

    'session' => [
        'timeout' => 7200,  // seconds
    ],

    'upload' => [
        'max_size'       => 5 * 1024 * 1024,  // 5 MB
        'allowed_images' => ['image/jpeg', 'image/png', 'image/webp'],
        'allowed_docs'   => ['application/pdf', 'image/jpeg', 'image/png'],
    ],
];
