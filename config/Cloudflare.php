<?php

return [
    'parser' => [
        'name'          => 'Cloudflare',
        'enabled'       => true,
        'sender_map'    => [
            '/abuse@cloudflare.com/',
        ],
        'body_map'      => [
            //
        ],
    ],

    'feeds' => [
        'default' => [
            'class'     => 'COPYRIGHT_INFRINGEMENT',
            'type'      => 'ABUSE',
            'enabled'   => true,
            'fields'    => [
                'IP',
                'timestamp',
                'domain',
            ],
        ],
    ],
];
