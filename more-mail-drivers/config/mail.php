<?php

/**
 * Third Party Mail Services
 *
 * @see https://laravel.com/docs/5.2/mail
 * @see https://github.com/laravel/laravel/blob/5.2/config/services.php
 */
return [

    'mailgun' => [
        'domain' => menv('MAILGUN_DOMAIN'),
        'secret' => menv('MAILGUN_SECRET'),
        'guzzle' => [
            'verify' => __DIR__.'/ca-bundle.crt'
        ],
    ],

    'mandrill' => [
        'secret' => menv('MANDRILL_SECRET'),
        'guzzle' => [
            'verify' => __DIR__.'/ca-bundle.crt'
        ],
    ],

    'ses' => [
        'key' => menv('SES_KEY'),
        'secret' => menv('SES_SECRET'),
        'region' => menv('SES_REGION'),
    ],

    'sparkpost' => [
        'secret' => menv('SPARKPOST_SECRET'),
        'guzzle' => [
            'verify' => __DIR__.'/ca-bundle.crt'
        ],
    ],

    'sendmail' => menv('SENDMAIL_COMMAND'),
];
