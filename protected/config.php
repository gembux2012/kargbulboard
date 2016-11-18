<?php

return [
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'dbname' => 'standard',
            'user' => 'root',
            'password' => 'Password00',
        ]
    ],
    'auth' => [
        'expire' => 31536000 // 1 year
    ],
    'mail' => [
        'method' => 'smtp',
        'host' => 'smtp.gmail.com',
        'auth' => [
            'username' => '2@gmail.com',
            'password' => ''
        ],
        'sender' => 'Sender',
        'port' => '587',
        'secure' => 'tls',
    ],
    'extensions' => [
        'jquery' => [
            'location' => 'local',
        ],
        'bootstrap' => [
            'location' => 'local',
            'theme' => '',
        ],
        'ckeditor' => [
            'location' => 'local',
        ],
        'ckfinder' => [
            'autoload' => false,
        ],
        'jstree' => [
            'autoload' => false,
        ],
        'sxgeo' => [
        ],
        'captcha' => [
            'register' => true,
            'message' => true,
        ],

    ],

    'published' =>'7',
    'domain' => 'www.karbukboard.ru',

    'prise' => [
        'nopaid' => '2',
        'word' => '5',
        'photo' => '100',
        'vip'  => '50'
    ]
];