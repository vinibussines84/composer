<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Este arquivo armazena as credenciais de serviços de terceiros como
    | Mailgun, Postmark, AWS, Pluggou e outros. Aqui é o local padrão
    | para configurar essas integrações externas no seu projeto.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'pluggou' => [
        'api_key' => env('PLUGGOU_API_KEY'),
        'organization_id' => env('PLUGGOU_ORGANIZATION_ID'),
    ],

];
